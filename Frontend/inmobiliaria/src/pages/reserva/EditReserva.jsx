import React, { useEffect, useState } from "react";
import Boton from "../../components/Boton";
import obtenerTabla from "../../utils/obtenerTabla";

//  devolver mensaje al editar resolver duda.
export default function EditReserva({ reserva, actualizar }) {
  const [reservaLocal, setReservaLocal] = useState({
    propiedad_id: reserva.propiedad_id || 0,
    inquilino_id: reserva.inquilino_id || 0,
    fecha_desde: reserva.fecha_desde || "",
    cantidad_noches: reserva.cantidad_noches || 0,
    valor_total: reserva.valor_total || 0,
  });
  const [propiedadesLocal, setPropiedadesLocal] = useState([]);
  const [inquilinosLocal, setInquilinosLocal] = useState([]);
  const [idsRelacionados, setIdsRelacionados] = useState([]);
  const [errores, setErrores] = useState({});

  const propiedades = async () => {
    const datos = await obtenerTabla("propiedades");
    const filtro = datos.filter((i) => i.id !== reserva.propiedad_id);
    const prop = datos.find((i) => i.id === reserva.propiedad_id);
    const propNueva = {
      tipo: "propiedad",
      id: prop.id,
      domicilio: prop.domicilio,
      localidad: prop.localidad_id,
    };

    const existePropNueva = idsRelacionados.some(
      (item) => item.id === propNueva.id
    );
    if (!existePropNueva) {
      setIdsRelacionados((idPrevios) => [...idPrevios, propNueva]);
    }

    return filtro;
  };

  const inquilinos = async () => {
    const datos = await obtenerTabla("inquilinos");
    const filtro = datos.filter((i) => i.id !== reserva.inquilino_id);
    const inq = datos.find((i) => i.id === reserva.inquilino_id);
    const inqNuevo = {
      tipo: "inquilino",
      id: inq.id,
      nombre: inq.nombre,
      apellido: inq.apellido,
    };

    const existeInqNuevo = idsRelacionados.some(
      (item) => item.id === inqNuevo.id
    );
    if (!existeInqNuevo) {
      setIdsRelacionados((idPrevios) => [...idPrevios, inqNuevo]);
    }

    return filtro;
  };

  useEffect(() => {
    propiedades()
      .then((props) => {
        setPropiedadesLocal(props);
      })
      .catch((error) => {
        console.error("Error fetching propiedades (en Edit):", error);
      });

    inquilinos()
      .then((inqs) => {
        setInquilinosLocal(inqs);
      })
      .catch((error) => {
        console.error("Error fetching inquilinos (en Edit):", error);
      });
  }, []);

  const handleEditar = async (r) => {
    try {
      const response = await fetch(`http://localhost/reservas/${reserva.id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(r),
      });

      const mensaje = await response.json();
      if (!response.ok) {
        throw new Error(`HTTP error! Error: ${JSON.stringify(mensaje)}`);
      }

      actualizar(mensaje);
    } catch (e) {
      console.error("Error editando reserva: ", e);
      console.dir(e); // Para ver las propiedades del error
    }
  };

  const validarForm = () => {
    let newErrors = {};

    if (!reservaLocal.propiedad_id)
      newErrors.propiedad_id = "La Propiedad es obligatoria.";
    if (!reservaLocal.inquilino_id)
      newErrors.inquilino_id = "El Inquilino es obligatorio.";
    if (!reservaLocal.cantidad_noches || isNaN(reservaLocal.cantidad_noches))
      newErrors.cantidad_noches =
        "La cantidad de noches es obligatoria y debe ser de tipo número.";
    if (reservaLocal.valor_total === 0 || isNaN(reservaLocal.valor_total))
      newErrors.valor_total =
        "El valor total es obligatorio y debe ser de tipo número.";

    if (!reservaLocal.fecha_desde.trim())
      newErrors.fecha_desde = "La fecha de inicio es obligatoria.";
    else {
      const fechaInicial = new Date(reserva.fecha_desde);
      const fechaDesde = new Date(reservaLocal.fecha_desde);
      const fechaActual = new Date();
      fechaActual.setHours(0, 0, 0, 0); // Ignorar la hora para comparar solo la fecha
      // Esto deberia ser un error que ni siquiera permita entrar al editor?
      if (fechaInicial <= fechaActual)
        newErrors.fecha_desde = "La fecha de la reserva ya ha comenzado.";
      else if (fechaDesde <= fechaActual)
        newErrors.fecha_desde =
          "La fecha de inicio debe ser posterior a la fecha actual.";
    }

    setErrores(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validarForm()) handleEditar(reservaLocal);
  };

  const handleChange = (e) => {
    const { name, value, type, selectedOptions } = e.target;
    let newValue = value;
    if (type === "select-one") {
      const selectedOption = selectedOptions[0];
      const dataType = selectedOption.getAttribute("data-type");
      if (dataType === "number") newValue = Number(value);
    } else if (type === "number") newValue = Number(value);

    setReservaLocal({ ...reservaLocal, [name]: newValue });
  };

  const propiedadPrecargada = () => {
    const prop = idsRelacionados.find(
      (propiedad) => propiedad.tipo === "propiedad"
    );
    if (prop) {
      return (
        prop.domicilio + "( ID: " + prop.id + " | Loc: " + prop.localidad + " )"
      );
    }
    return "";
  };

  const inquilinoPrecargado = () => {
    const inq = idsRelacionados.find(
      (inquilino) => inquilino.tipo === "inquilino"
    );
    if (inq) {
      return inq.nombre + " " + inq.apellido + "( ID: " + inq.id + " )";
    }
    return "";
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
          <label className="text-slate-200 font-semibold input-title">
            Propiedad:
            <select
              name="propiedad_id"
              title="Propiedad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              value={reservaLocal.propiedad_id}
              onChange={handleChange}
            >
              <option value={reserva.propiedad_id} data-type="number">
                {propiedadPrecargada()}
              </option>
              {propiedadesLocal.map((propiedad) => (
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
          <label className="text-slate-200 font-semibold input-title">
            Inquilino:
            <select
              name="inquilino_id"
              title="Inquilino"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              value={reservaLocal.inquilino_id}
              onChange={handleChange}
            >
              <option value={reserva.inquilino_id} data-type="number">
                {inquilinoPrecargado()}
              </option>
              {inquilinosLocal.map((inquilino) => (
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
          <label className="text-slate-200 font-semibold input-title">
            Fecha desde:
            <input
              name="fecha_desde"
              type="date"
              title="Fecha desde"
              className="bg-slate-800 text-slate-200 rounded-lg p-3 mx-3"
              value={reservaLocal.fecha_desde}
              onChange={handleChange}
            />
            {errores.fecha_desde && (
              <div className="bg-red-800 text-white">{errores.fecha_desde}</div>
            )}
          </label>
          <label className="text-slate-200 font-semibold input-title">
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
          <label className="text-slate-200 font-semibold input-title">
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
            Editar
          </button>
        </form>
      </div>
    </div>
  );
}
