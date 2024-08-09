import React, { useEffect, useState, useCallback } from "react";
import Boton from "../../components/Boton";
import EditTipoPropiedad from "./EditTipoPropiedad";
import NewTipoPropiedad from "./NewTipoPropiedad";
import Advertencia from "../../components/Advertencia";
import MensajeError from "../../components/MensajeError";

// DUDA
// No puedo mostrar correctamente los mensajes recibidos de la operacion del endpoint
// Por que esta mal que diga que la propiedad es obligatoria cuando se intenta enviar el formulario sin datos
// El editar si chequea el tipo de dato.
// El catch es lo mismo que el !response.ok???
// Me falta acomodar los mensajes al terminar una accion. Los pase a los componentes y me quedo lo que antes mostraba aca.

export default function TipoPropiedadPage() {
  const [propiedades, setPropiedades] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [feedback, setFeedback] = useState(null);
  const [estadoApp, setEstadoApp] = useState("normal");
  const [elementId, setElementId] = useState(null);

  const fetchPropiedades = async () => {
    try {
      const response = await fetch("http://localhost/tipos_propiedad");
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();
      setPropiedades(data["Data"]);
      setLoading(false);
    } catch (e) {
      setError(e);
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPropiedades();
  }, []);

  useEffect(() => {
    if (estadoApp === "normal") setElementId(null);
  }, [estadoApp]);

  const handleEliminar = async (id) => {
    try {
      const response = await fetch(`http://localhost/tipos_propiedad/${id}`, {
        method: "DELETE",
      });
      if (!response.ok) {
        const errorData = await response.json();
        setEstadoApp("error");
        setError({
          Codigo: errorData.Codigo,
          Status: errorData.Status,
          Mensaje: errorData.Mensaje,
        });
        throw new Error(`HTTP error! status: ${response.status}`);
      } else {
        setPropiedades((prevPropiedades) =>
          prevPropiedades.filter((propiedad) => propiedad.id !== id)
        );
        setElementId(null);
      }
    } catch (e) {
      console.error("Error eliminando propiedad: ", e);
    }
  };

  const actualizarEstado = useCallback((m) => {
    if (m != null) {
      setFeedback(m);
      if (m === "Success") {
        setEstadoApp("agregado");
        fetchPropiedades();
      } else {
        setEstadoApp("fallido");
      }
    } else {
      setEstadoApp("normal");
    }
  }, []);

  if (loading)
    return (
      <div className="w-full inline-flex justify-center items-center space-x-3 mt-24">
        <h2 className="text-3xl font-semibold animate-pulse">Cargando...</h2>
      </div>
    );

  // Algun error que requiera recargar la pagina. Independiente de la acción del usuario
  if (error && estadoApp !== "error") {
    return (
      <div className="w-full inline-flex justify-center items-center space-x-3 mt-24">
        <h3 className="text-3xl font-semibold text-red-500">{error}</h3>
      </div>
    );
  }

  return (
    <section className="container inline-flex justify-center items-center space-x-3 mt-24">
      <Boton tipo="agregar" onClick={() => setEstadoApp("agregando")} />
      {estadoApp === "agregando" && (
        <NewTipoPropiedad actualizar={actualizarEstado} />
      )}
      <ul>
        {(estadoApp === "editado" || estadoApp === "agregado") && (
          <section className={"bg-green-200"}>
            <Boton tipo="cancelar" onClick={() => setEstadoApp("normal")} />
            <h3>{feedback}</h3>
          </section>
        )}
        {estadoApp === "fallido" && (
          <MensajeError
            err={feedback}
            actualizar={() => setEstadoApp("normal")}
          />
        )}
        {estadoApp === "error" && (
          <MensajeError
            err={error}
            actualizar={() => {
              setEstadoApp("normal");
              setError(null);
            }}
          />
        )}
        {propiedades.map((propiedad) => (
          <li
            key={propiedad.id}
            className="p-4 min-w-96 border border-gray-300 rounded-lg shadow-md flex"
          >
            <h4 className="text-xl inline">{propiedad.id}.</h4>
            {elementId === propiedad.id && estadoApp === "editando" ? (
              <EditTipoPropiedad
                propiedad={propiedad}
                actualizar={(m) => {
                  if (m != null) {
                    setFeedback(m);
                    if (m === "Success") {
                      setEstadoApp("editado");
                      fetchPropiedades();
                    } else {
                      setEstadoApp("fallido");
                    }
                  } else {
                    setEstadoApp("normal");
                  }
                }}
              />
            ) : (
              <h4 className="text-xl font-semibold inline">
                {propiedad.nombre}
              </h4>
            )}
            <div className="inline-flex ml-auto space-x-4">
              {elementId === propiedad.id && estadoApp === "eliminando" ? (
                <Advertencia
                  elemento={propiedad.nombre}
                  onEstado={(estado) =>
                    estado === "confirmar"
                      ? handleEliminar(propiedad.id)
                      : setEstadoApp("normal")
                  }
                />
              ) : (
                elementId !== propiedad.id &&
                estadoApp !== "editando" && (
                  <>
                    <Boton
                      tipo="editar"
                      onClick={() => {
                        setElementId(propiedad.id);
                        setEstadoApp("editando");
                      }}
                    />
                    <Boton
                      tipo="eliminar"
                      onClick={() => {
                        setElementId(propiedad.id);
                        setEstadoApp("eliminando");
                      }}
                    />
                  </>
                )
              )}
            </div>
          </li>
        ))}
      </ul>
    </section>
  );
}
