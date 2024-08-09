import React, { useState, useEffect } from "react";
import Boton from "../../components/Boton";
import NewReserva from "./NewReserva";
import EditReserva from "./EditReserva";
import Advertencia from "../../components/Advertencia";
import conversorDataRelacional from "../../utils/conversorDataRelacional";

// DUDA
//Resolver el dataRelacional
//Mostrar error al intentar editar o borrar una reserva que ya esta en curso

export default function ReservaPage() {
  const [reservas, setReservas] = useState([]);
  const [dataRelacional, setDataRelacional] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false); // El error indica que algo salio mal. No depende del input del usuario
  const [feedback, setFeedback] = useState(null); // Estado de la solicitud del usuario
  const [estadoApp, setEstadoApp] = useState("normal");
  const [elementId, setElementId] = useState(null);

  const fetchReservas = async () => {
    try {
      const responseReservas = await fetch("http://localhost/reservas");
      if (!responseReservas.ok)
        throw new Error(`HTTP error! status: ${responseReservas.status}`);

      const dataReservas = await responseReservas.json();
      const dataRelacionalResult = await conversorDataRelacional(
        dataReservas["Data"],
        "inquilinos",
        "propiedades"
      );

      setReservas(dataReservas["Data"]);
      setDataRelacional(dataRelacionalResult);
      setLoading(false);
    } catch (e) {
      setError(e);
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchReservas();
  }, []);

  if (loading) {
    return (
      <div className="w-full inline-flex justify-center items-center space-x-3 mt-24">
        <h2 className="text-3xl font-semibold animate-pulse">Cargando...</h2>
      </div>
    );
  }

  if (error) {
    return (
      <div className="w-full inline-flex justify-center items-center space-x-3 mt-24">
        <h3 className="text-3xl font-semibold text-red-500">{error}</h3>
      </div>
    );
  }

  const handleEliminar = async (id) => {
    try {
      const response = await fetch(`http://localhost/reservas/${id}`, {
        method: "DELETE",
      });
      const mensaje = await response.json();
      setFeedback({
        Status: mensaje.Status,
        Codigo: mensaje.codigo,
        Mensaje: mensaje.Mensaje,
        Data: mensaje.data,
      });
      response.ok ? setEstadoApp("success") : setEstadoApp("fallido");

      setReservas((prevReservas) =>
        prevReservas.filter((reserva) => reserva.id !== id)
      );
      fetchReservas();
      setElementId(null);
    } catch (e) {
      console.error("Error eliminando reserva: ", e);
      setError(e);
    }
  };

  // tuve que hacer esta funcion porque recibo errores de otras tablas como objetos.
  // recibe los mensajes por clave
  const imprimirFeedback = () => {
    let mensajes = [];
    for (const key in feedback.Mensaje) {
      if (feedback.Mensaje.hasOwnProperty(key)) {
        mensajes.push(feedback.Mensaje[key]);
      }
    }
    return mensajes;
  };

  return (
    <section className="w-full inline-flex flex-col justify-center space-y-3 mt-14">
      <Boton tipo="agregar" onClick={() => setEstadoApp("agregando")} />
      {estadoApp === "agregando" && (
        <NewReserva
          actualizar={(m) => {
            if (m != null) {
              setFeedback({
                Status: m.Status,
                Codigo: m.codigo,
                Mensaje: m.Mensaje,
                Data: m.data,
              });
              if (m.Status === "Success") {
                setEstadoApp("agregado");
                fetchReservas();
              } else setEstadoApp("fallido");
            } else setEstadoApp("normal");
          }}
        />
      )}
      {(estadoApp === "editado" ||
        estadoApp === "agregado" ||
        estadoApp === "success" ||
        estadoApp === "fallido") && (
        <section
          className={estadoApp === "fallido" ? "bg-red-200" : "bg-green-200"}
        >
          <Boton tipo="cancelar" onClick={() => setEstadoApp("normal")}></Boton>
          <h3>{feedback.Status}</h3>
          <p>{imprimirFeedback()}</p>
        </section>
      )}
      <ul className="space-y-2">
        {reservas.map((reserva, index) => (
          <li
            key={reserva.id}
            className="p-4 border border-gray-300 rounded-lg shadow-md flex flex-col space-y-2"
          >
            <h4 className="text-xl inline">{reserva.id}.</h4>
            {estadoApp !== "editando" ? (
              <>
                <h4 className="text-xl inline">
                  {"Propiedad: " + dataRelacional[index]?.propiedad_id}.
                </h4>
                <h4 className="text-xl inline">
                  {"Inquilino: " + dataRelacional[index]?.inquilino_id}.
                </h4>
                <h4 className="text-xl inline">
                  {"Fecha de reserva desde: " + reserva.fecha_desde}.
                </h4>
                <h4 className="text-xl inline">
                  {"Cantidad de noches: " + reserva.cantidad_noches}.
                </h4>
                <h4 className="text-xl inline">
                  {"Valor total de reserva: " + reserva.valor_total}.
                </h4>
              </>
            ) : (
              estadoApp === "editando" &&
              reserva.id === elementId && (
                <EditReserva
                  reserva={reserva}
                  actualizar={(m) => {
                    fetchReservas();
                    if (m != null) {
                      setFeedback(m);
                      if (m.Status === "Success") setEstadoApp("editado");
                    } else setEstadoApp("normal");
                  }}
                />
              )
            )}

            <div className="inline-flex ml-auto space-x-4">
              {reserva.id === elementId && estadoApp === "eliminando" ? (
                <Advertencia
                  elemento={
                    dataRelacional[index].propiedad_id +
                    " de " +
                    dataRelacional[index].inquilino_id
                  }
                  onEstado={(estado) =>
                    estado === "confirmar"
                      ? handleEliminar(reserva.id)
                      : setEstadoApp("normal")
                  }
                />
              ) : (
                <>
                  <Boton
                    tipo="editar"
                    onClick={() => {
                      setElementId(reserva.id);
                      setEstadoApp("editando");
                    }}
                  />
                  <Boton
                    tipo="eliminar"
                    onClick={() => {
                      setElementId(reserva.id);
                      setEstadoApp("eliminando");
                    }}
                  />
                </>
              )}
            </div>
          </li>
        ))}
      </ul>
    </section>
  );
}
