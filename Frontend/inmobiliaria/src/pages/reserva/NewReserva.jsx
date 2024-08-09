import React, { useEffect, useState } from "react";
import Boton from "../../components/Boton";
import obtenerTabla from "../../utils/obtenerTabla";

// DUDA
// Me falta actualizar la pagina con el nuevo dato ingresado por metodo post - RESOLVER DUDA
// Hacer un fetch de las propiedades y los inquilinos
// Al hacer una reserva, la propiedad debe dejar de estar disponible + chequear cant de dias con dias disponible de la prop + cuando se cumplen los dias, la reserva se borra y la propiedad vuelve a estar disponible.

export default function NewReserva({ actualizar }) {
  const estadoInicial = {
    propiedad_id: "",
    inquilino_id: "",
    fecha_desde: "",
    cantidad_noches: 0,
    valor_total: 0,
  };
  const [reserva, setReserva] = useState(estadoInicial);
  const [propiedades, setPropiedades] = useState([]);
  const [inquilinos, setInquilinos] = useState([]);
  const [errores, setErrores] = useState({});

  const cargarDatos = async () => {
    const dataPropiedades = await obtenerTabla("propiedades");
    const dataInquilinos = await obtenerTabla("inquilinos");

    setPropiedades(dataPropiedades);
    setInquilinos(dataInquilinos);
  };

  useEffect(() => {
    cargarDatos();
  }, []);

  const handleAgregar = async (reserva) => {
    try {
      console.log("handle: " + reserva);
      console.log("handle: " + propiedades);
      console.log("handle: " + inquilinos);
      const response = await fetch("http://localhost/reservas", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(reserva),
      });
      const mensaje = await response.json();
      if (!response.ok) {
        setErrores(mensaje.Status + ": " + mensaje.Mensaje);
        console.error(mensaje);
      } else {
        setReserva(estadoInicial);
      }
      actualizar(mensaje);
    } catch (e) {
      console.error("Error agregando reserva: ", e);
    }
  };

  const resetForm = () => {
    setReserva(estadoInicial);
    setErrores({});
  };

  const validarForm = () => {
    let newErrors = {};

    if (!reserva.propiedad_id)
      newErrors.propiedad_id = "La Propiedad es obligatoria.";
    if (!reserva.inquilino_id)
      newErrors.inquilino_id = "El Inquilino es obligatorio.";
    if (!reserva.cantidad_noches || isNaN(reserva.cantidad_noches))
      newErrors.cantidad_noches =
        "La cantidad de noches es obligatoria y debe ser de tipo número.";
    if (reserva.valor_total === 0 || isNaN(reserva.valor_total))
      newErrors.valor_total =
        "El valor total es obligatorio y debe ser de tipo número.";

    if (!reserva.fecha_desde.trim())
      newErrors.fecha_desde = "La fecha de inicio es obligatoria.";
    else {
      const fechaDesde = new Date(reserva.fecha_desde);
      const fechaActual = new Date();
      fechaActual.setHours(0, 0, 0, 0); // Ignorar la hora para comparar solo la fecha
      if (fechaDesde <= fechaActual)
        newErrors.fecha_desde =
          "La fecha de inicio debe ser posterior a la fecha actual.";
    }

    setErrores(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log(reserva);
    console.log(propiedades);
    console.log(inquilinos);
    if (validarForm()) {
      handleAgregar(reserva);
      //resetForm(); // Reiniciar el formulario después de agregar una reserva
    }
  };

  const handleChange = (e) => {
    const { name, value, type, selectedOptions } = e.target;
    let newValue = value;
    if (type === "select-one") {
      const selectedOption = selectedOptions[0];
      const dataType = selectedOption.getAttribute("data-type");
      if (dataType === "number") newValue = Number(value);
    } else if (type === "number") newValue = Number(value);

    setReserva({ ...reserva, [name]: newValue });
  };

  return (
    <section className="modal">
      <div className="modal-overlay"></div>
      <div className="modal-content shadow-nav-shadow h-4/5 overflow-y-scroll">
        <Boton
          className="mb-6"
          tipo={"cancelar"}
          onClick={() => {
            actualizar(null);
            resetForm();
          }}
        />
        <form
          className="flex flex-col space-y-4 w-full"
          onSubmit={handleSubmit}
        >
          <label className="text-zinc-200 font-semibold input-title">
            Propiedad:
            <select
              name="propiedad_id"
              title="Propiedad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              value={reserva.propiedad_id}
              onChange={handleChange}
            >
              <option disabled value="">
                Seleccione una propiedad
              </option>
              {propiedades.map((propiedad) => (
                <option
                  className="ml-3"
                  key={propiedad.id}
                  value={propiedad.id}
                  data-type="number"
                >
                  {propiedad.domicilio +
                    " ( ID: " +
                    propiedad.id +
                    " | Loc: " +
                    propiedad.localidad_id +
                    " )"}
                </option>
              ))}
            </select>
            {errores.propiedad_id && (
              <div className="bg-red-800 text-white">
                {errores.propiedad_id}
              </div>
            )}
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Inquilino:
            <select
              name="inquilino_id"
              title="Inquilino"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              value={reserva.inquilino_id}
              onChange={handleChange}
            >
              <option disabled value="">
                Seleccione un inquilino
              </option>
              {inquilinos.map((inquilino) => (
                <option
                  className="mx-3"
                  key={inquilino.id}
                  value={inquilino.id}
                  data-type="number"
                >
                  {inquilino.nombre +
                    " " +
                    inquilino.apellido +
                    " ( ID: " +
                    inquilino.id +
                    " )"}
                </option>
              ))}
            </select>
            {errores.inquilino_id && (
              <div className="bg-red-800 text-white">
                {errores.inquilino_id}
              </div>
            )}
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Fecha desde:
            <input
              name="fecha_desde"
              type="date"
              title="Fecha desde"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              value={reserva.fecha_desde}
              onChange={handleChange}
            />
            {errores.fecha_desde && (
              <div className="bg-red-800 text-white">{errores.fecha_desde}</div>
            )}
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de noches:
            <input
              name="cantidad_noches"
              type="number"
              title="Cantidad de noches"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              defaultValue={reserva.cantidad_noches}
              onChange={handleChange}
            />
            {errores.cantidad_noches && (
              <div className="bg-red-800 text-white">
                {errores.cantidad_noches}
              </div>
            )}
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Valor total:
            <input
              name="valor_total"
              type="number"
              title="Valor total"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3 input-number"
              defaultValue={reserva.valor_total}
              onChange={handleChange}
            />
            {errores.valor_total && (
              <div className="bg-red-800 text-white">{errores.valor_total}</div>
            )}
          </label>
          <button
            className="btn shadow-nav-shadow w-1/2 self-center"
            type="submit"
          >
            Agregar
          </button>
        </form>
      </div>
    </section>
  );
}
