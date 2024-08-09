import React, { useState } from "react";
import Boton from "../../components/Boton";

const NewTipoPropiedad = ({ actualizar, localidades, tipos_propiedad }) => {
  const estadoInicial = {
    domicilio: "",
    localidad_id: "",
    cochera: false,
    cantidad_huespedes: 0,
    fecha_inicio_disponibilidad: "",
    cantidad_dias: 0,
    disponible: true,
    valor_noche: 0,
    tipo_propiedad_id: "",
  };

  const [propiedad, setPropiedad] = useState(estadoInicial);
  const [errores, setErrores] = useState("");

  const handleAgregar = async (propiedad) => {
    try {
      const response = await fetch("http://localhost/propiedades", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(propiedad),
      });
      const mensaje = await response.json();
      if (!response.ok) {
        setErrores(mensaje.Status + ": " + mensaje.Mensaje);
        console.error(response);
        throw new Error(`HTTP error! status: ${response.status}`);
      } else {
        setPropiedad(estadoInicial);
        actualizar(mensaje);
      }
    } catch (e) {
      console.error("Error agregando propiedad: ", e);
      console.dir("Error agregando propiedad: ", e);
    }
  };

  const validarForm = () => {
    let newErrors = {};
    const campoObligatorio =
      "*Campo obligatorio. No se permite enviarlo vacio.";
    const campoObligatorioSelect = "*Campo obligatorio. Elija una opción.";
    const campoNumerico = "Solo se admiten valores numericos mayores a 0.";

    // Domicilio
    const domicilioRegex = /^[A-Za-z0-9 ]+$/;
    if (!propiedad.domicilio.trim()) newErrors.domicilio = campoObligatorio;
    else if (!domicilioRegex.test(propiedad.domicilio))
      newErrors.domicilio = "Solo se admiten caracteres alfanumericos.";
    // localidad id
    if (!propiedad.localidad_id)
      newErrors.localidad_id = campoObligatorioSelect;
    // Cantidad huespedes
    if (!propiedad.cantidad_huespedes)
      newErrors.cantidad_huespedes = campoObligatorio;
    else if (
      isNaN(propiedad.cantidad_huespedes) ||
      propiedad.cantidad_huespedes <= 0
    )
      newErrors.cantidad_huespedes = campoNumerico;
    // Fecha inicio disponibilidad
    if (!propiedad.fecha_inicio_disponibilidad.trim())
      newErrors.fecha_inicio_disponibilidad = campoObligatorio;
    // Cantidad dias
    if (!propiedad.cantidad_dias) newErrors.cantidad_dias = campoObligatorio;
    else if (isNaN(propiedad.cantidad_dias) || propiedad.cantidad_dias <= 0)
      newErrors.cantidad_dias = campoNumerico;
    // Valor noche
    if (!propiedad.valor_noche) newErrors.valor_noche = campoObligatorio;
    else if (isNaN(propiedad.valor_noche) || propiedad.valor_noche <= 0)
      newErrors.valor_noche = campoNumerico;
    // Tipo propiedad
    if (!propiedad.tipo_propiedad_id)
      newErrors.tipo_propiedad_id = campoObligatorioSelect;

    // opcionales
    // Cantidad habitaciones
    if (propiedad.cantidad_habitaciones) {
      if (
        isNaN(propiedad.cantidad_habitaciones) ||
        propiedad.cantidad_habitaciones <= 0
      )
        newErrors.cantidad_habitaciones = campoNumerico;
    }
    // Cantidad banios
    if (propiedad.cantidad_banios) {
      if (isNaN(propiedad.cantidad_banios) || propiedad.cantidad_banios <= 0)
        newErrors.cantidad_banios = campoNumerico;
    }
    // Imagen
    /* if (propiedad.imagen) {
      const base64Regex = /^data:image\/([a-z0-9]+);base64,(.+)$/;
      if (!base64Regex.test(propiedad.imagen))
        newErrors.imagen =
          "El formato de la imagen es incorrecto. Solo se admite formato base64.";
    }*/

    setErrores(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleImage = (e) => {
    if (e.target.files) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const base64 = reader.result;
        const parts = base64.match(/^data:image\/([a-z0-9]+);base64,(.+)$/);
        setPropiedad({ ...propiedad, tipo_imagen: parts[1], imagen: parts[2] });
      };
      reader.readAsDataURL(e.target.files[0]);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validarForm()) {
      handleAgregar(propiedad);
      console.log(propiedad);
    }
  };

  return (
    <div className="modal">
      <div className="modal-overlay"></div>
      <div className="modal-content shadow-nav-shadow h-4/5 overflow-y-scroll">
        <Boton
          className="mb-6"
          tipo={"cancelar"}
          onClick={() => actualizar(null)}
        />
        <form
          className="flex flex-col space-y-4 w-full"
          onSubmit={handleSubmit}
        >
          <label className="text-zinc-200 font-semibold input-title">
            Domicilio:
            <input
              name="domicilio"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              value={propiedad.domicilio}
              onChange={(e) =>
                setPropiedad({ ...propiedad, [e.target.name]: e.target.value })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.domicilio}</div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Localidad:
            <select
              name="localidad_id"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              value={propiedad.localidad_id}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            >
              <option disabled value="">
                Seleccione una localidad
              </option>
              {localidades.map((localidad) => (
                <option
                  className="ml-3"
                  key={localidad.id}
                  value={localidad.id}
                >
                  {localidad.nombre}
                </option>
              ))}
            </select>
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.localidad_id}</div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de habitaciones:
            <input
              name="cantidad_habitaciones"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_habitaciones}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">
              {errores.cantidad_habitaciones}
            </div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de baños:
            <input
              name="cantidad_banios"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_banios}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">
              {errores.cantidad_banios}
            </div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Cochera:
            <input
              name="cochera"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="checkbox"
              value={propiedad.cochera}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Boolean(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.cochera}</div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de huespedes:
            <input
              name="cantidad_huespedes"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_huespedes}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">
              {errores.cantidad_huespedes}
            </div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Fecha de inicio de disponibilidad:
            <input
              name="fecha_inicio_disponibilidad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="date"
              value={propiedad.fecha_inicio_disponibilidad}
              onChange={(e) =>
                setPropiedad({ ...propiedad, [e.target.name]: e.target.value })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">
              {errores.fecha_inicio_disponibilidad}
            </div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de días:
            <input
              name="cantidad_dias"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_dias}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.cantidad_dias}</div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Disponible:
            <input
              name="disponible"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="checkbox"
              value={propiedad.disponible}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Boolean(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.disponible}</div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Valor por noche:
            <input
              name="valor_noche"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.valor_noche}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.valor_noche}</div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Tipo de propiedad:
            <select
              name="tipo_propiedad_id"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              value={propiedad.tipo_propiedad_id}
              onChange={(e) =>
                setPropiedad({
                  ...propiedad,
                  [e.target.name]: Number(e.target.value),
                })
              }
            >
              <option disabled value="">
                Seleccione un tipo de propiedad
              </option>
              {tipos_propiedad.map((tipo) => (
                <option className="ml-3" key={tipo.id} value={tipo.id}>
                  {tipo.nombre}
                </option>
              ))}
            </select>
          </label>
          {errores && (
            <div className="bg-red-800 text-white">
              {errores.tipo_propiedad_id}
            </div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Imagen de la propiedad:
            <input
              name="imagen"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="file"
              accept="image/*"
              onChange={handleImage}
            />
          </label>
          <div>
            {propiedad.imagen && (
              <img
                src={`data:image/${propiedad.tipo_imagen};base64,${propiedad.imagen}`}
                alt="Imagen de la propiedad"
              />
            )}
          </div>
          {errores && (
            <div className="bg-red-800 text-white">{errores.imagen}</div>
          )}
          <button
            className="btn shadow-nav-shadow w-1/2 self-center"
            type="submit"
          >
            Agregar
          </button>
        </form>
      </div>
    </div>
  );
};

export default NewTipoPropiedad;
