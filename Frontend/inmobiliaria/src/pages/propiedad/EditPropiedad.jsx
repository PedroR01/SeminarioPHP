import React, { useEffect, useState } from "react";
import Boton from "../../components/Boton";

export default function EditPropiedad({
  actualizar,
  propiedad,
  localidades,
  tipos_propiedad,
}) {
  const [propiedadLocal, setPropiedadLocal] = useState({
    domicilio: propiedad.domicilio || "",
    localidad_id: propiedad.localidad_id || 0,
    cantidad_habitaciones: propiedad.cantidad_habitaciones || null,
    cantidad_banios: propiedad.cantidad_banios || null,
    cochera: propiedad.cochera === 1 ? true : false,
    cantidad_huespedes: propiedad.cantidad_huespedes || 0,
    fecha_inicio_disponibilidad: propiedad.fecha_inicio_disponibilidad || "",
    cantidad_dias: propiedad.cantidad_dias || 0,
    disponible: propiedad.disponible === 1 ? true : false,
    valor_noche: propiedad.valor_noche || 0,
    tipo_propiedad_id: propiedad.tipo_propiedad_id || 0,
  });
  const [errores, setErrores] = useState("");

  useEffect(() => {
    setPropiedadLocal({
      ...propiedadLocal,
      localidad_id: localidades.find(
        (loc) => loc.nombre === propiedad.localidad_id
      ).id,
      tipo_propiedad_id: tipos_propiedad.find(
        (tipo) => tipo.nombre === propiedad.tipo_propiedad_id
      ).id,
    });
  }, []);

  const handleEditar = async (id, prop) => {
    try {
      const response = await fetch(`http://localhost/propiedades/${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(prop),
      });
      const mensaje = await response.json();
      if (!response.ok) {
        setErrores(mensaje.Status + ": " + mensaje.Mensaje);
        console.error(mensaje);
        //throw new Error(`HTTP error! status: ${response.status}`);
      } else {
        actualizar(mensaje);
      }
    } catch (e) {
      console.error("Error agregando propiedad: ", e);
      console.dir("Error agregando propiedad: ", e);
    }
  };

  const validarForm = () => {
    let newErrors = {};

    if (!propiedadLocal.domicilio)
      newErrors.domicilio = "El domicilio es obligatorio.";
    if (!propiedadLocal.localidad_id)
      newErrors.localidad_id = "La localidad es obligatoria.";
    if (!propiedadLocal.tipo_propiedad_id)
      newErrors.tipo_propiedad_id = "El tipo de propiedad es obligatorio.";
    if (
      !propiedadLocal.cantidad_huespedes === 0 ||
      isNaN(propiedadLocal.cantidad_huespedes)
    )
      newErrors.cantidad_huespedes =
        "La cantidad de huespedes es obligatoria y debe ser de tipo número.";
    if (!propiedadLocal.valor_noche === 0 || isNaN(propiedadLocal.valor_noche))
      newErrors.valor_noche =
        "El valor por noche es obligatorio y debe ser de tipo número.";

    if (!propiedadLocal.fecha_inicio_disponibilidad.trim())
      newErrors.fecha_inicio_disponibilidad =
        "La fecha de inicio es obligatoria.";
    else {
      const fechaInicial = new Date(propiedadLocal.fecha_inicio_disponibilidad);
      const fechaActual = new Date();
      fechaActual.setHours(0, 0, 0, 0); // Ignorar la hora para comparar solo la fecha

      if (fechaInicial <= fechaActual)
        newErrors.fecha_desde =
          "La fecha de inicio debe ser mayor a la actual.";
    }

    setErrores(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validarForm()) handleEditar(propiedad.id, propiedadLocal);
    console.log(propiedadLocal);
  };

  const handleImage = (e) => {
    if (e.target.files) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const base64 = reader.result;
        setPropiedadLocal({ ...propiedadLocal, [e.target.name]: base64 });
      };
      reader.readAsDataURL(e.target.files[0]);
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
              value={propiedadLocal.domicilio}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
                  [e.target.name]: e.target.value,
                })
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
              value={propiedadLocal.localidad_id}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
                  [e.target.name]: Number(e.target.value),
                })
              }
            >
              <option
                value={
                  localidades.find(
                    (localidad) => localidad.nombre === propiedad.localidad_id
                  ).id
                }
              >
                {propiedad.localidad_id}
              </option>
              {localidades.map((localidad) => {
                if (localidad.nombre !== propiedad.localidad_id)
                  return (
                    <option
                      className="ml-3"
                      key={localidad.id}
                      value={localidad.id}
                    >
                      {localidad.nombre}
                    </option>
                  );
                else return null;
              })}
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
              placeholder="Sin especificar"
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
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
              placeholder="Sin especificar"
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
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
            {
              <input
                name="cochera"
                className="bg-slate-800 text-slate-200 rounded-lg p-3"
                type="checkbox"
                checked={propiedadLocal.cochera}
                onClick={(e) => {
                  setPropiedadLocal({
                    ...propiedadLocal,
                    [e.target.name]: e.target.checked === false ? 0 : true,
                  });
                }}
              />
            }
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
              defaultValue={propiedadLocal.cantidad_huespedes}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
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
              value={propiedadLocal.fecha_inicio_disponibilidad}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
                  [e.target.name]: e.target.value,
                })
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
              defaultValue={propiedadLocal.cantidad_dias}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
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
            {
              <input
                name="disponible"
                className="bg-slate-800 text-slate-200 rounded-lg p-3"
                type="checkbox"
                checked={propiedadLocal.disponible}
                onClick={(e) => {
                  setPropiedadLocal({
                    ...propiedadLocal,
                    [e.target.name]: e.target.checked === false ? 0 : true,
                  });
                }}
              />
            }
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
              defaultValue={propiedadLocal.valor_noche}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
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
              value={propiedadLocal.tipo_propiedad_id}
              onChange={(e) =>
                setPropiedadLocal({
                  ...propiedadLocal,
                  [e.target.name]: Number(e.target.value),
                })
              }
            >
              <option
                value={
                  tipos_propiedad.find(
                    (tipo) => tipo.nombre === propiedad.tipo_propiedad_id
                  ).id
                }
              >
                {propiedad.tipo_propiedad_id}
              </option>
              {tipos_propiedad.map((tipo) => {
                if (tipo.nombre !== propiedad.tipo_propiedad_id)
                  return (
                    <option className="ml-3" key={tipo.id} value={tipo.id}>
                      {tipo.nombre}
                    </option>
                  );
                else return null;
              })}
            </select>
          </label>
          {errores && (
            <div className="bg-red-800 text-white">
              {errores.tipo_propiedad_id}
            </div>
          )}
          <label className="text-zinc-200 font-semibold input-title">
            Imagen de la propiedad:
            {propiedadLocal.imagen ? (
              <img src={propiedad.imagen} alt="Imagen de la propiedad" />
            ) : null}
            <input
              name="imagen"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="file"
              accept="image/*"
              onChange={handleImage}
            />
          </label>
          {errores && (
            <div className="bg-red-800 text-white">{errores.imagen}</div>
          )}
          <button
            className="btn shadow-nav-shadow w-1/2 self-center"
            type="submit"
          >
            Modificar
          </button>
        </form>
      </div>
    </div>
  );
}
