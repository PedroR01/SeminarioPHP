import React, { useEffect, useState } from "react";
import Boton from "../../components/Boton";
import DetailPropiedad from "./DetailPropiedad";
import NewPropiedad from "./NewPropiedad";
import EditPropiedad from "./EditPropiedad";
import Advertencia from "../../components/Advertencia";
import FiltroPropiedades from "../../components/FiltroPropiedades";

// DUDA
//Faltan corroborar los valores de los campos y chequear las cargas de imagenes al editar y al añadir.
//En editar, al hacer cambios sin hacer el submit, en el listado cambían los valores. RESOLVER
//Cambiar los estados de la app para operaciones a "success" o "fail" ==> m.Status

// NEW
// No funciona al querer subir la imagen base64 --> Imagen excede el tamaño limite de base64? Consultar a los profes O TRANSFORMAR CADENA A TIPO TEXT?
// Por que cambia el valor en los inputs de numero despues de que agrego un valor? --> Tengo que desactivar el funcionamiento de las flechitas del input, solo las oculte.

// DETAIL
// Visualizar la imagen base64

// FILTRADO
// Arreglar el estilo y como se muestran las propiedades.

export default function PropiedadPage() {
  const [propiedades, setPropiedades] = useState([]);
  const [localidades, setLocalidades] = useState([]);
  const [tipoPropiedades, setTipoPropiedades] = useState([]);
  const [filtrado, setFiltrado] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [estadoApp, setEstadoApp] = useState("normal");
  const [elementId, setElementId] = useState(null);
  const [feedback, setFeedback] = useState(null);

  const fetchPropiedades = async () => {
    try {
      const responsePropiedad = await fetch("http://localhost/propiedades");
      const responseTipoPropiedad = await fetch(
        "http://localhost/tipos_propiedad"
      );
      const responseLocalidad = await fetch("http://localhost/localidades");

      if (
        !responsePropiedad.ok ||
        !responseTipoPropiedad.ok ||
        !responseLocalidad.ok
      ) {
        throw new Error(
          `HTTP error! status: Propiedad: ${responsePropiedad.status}, Tipo Propiedad: ${responseTipoPropiedad.status}, Localidad: ${responseLocalidad.status}`
        );
      }

      const responseP = await responsePropiedad.json();
      const responseTP = await responseTipoPropiedad.json();
      const responseL = await responseLocalidad.json();

      const data = responseP["Data"].map((propiedad) => {
        responseTP["Data"].forEach((tipo) => {
          if (tipo.id === propiedad.tipo_propiedad_id) {
            propiedad.tipo_propiedad_id = tipo.nombre;
          }
        });
        responseL["Data"].forEach((localidad) => {
          if (localidad.id === propiedad.localidad_id) {
            propiedad.localidad_id = localidad.nombre;
          }
        });
        return propiedad;
      });

      setLocalidades(responseL["Data"]);
      setTipoPropiedades(responseTP["Data"]);
      setPropiedades(data);
      setFiltrado(data);
      setLoading(false);
    } catch (e) {
      setError(e.message);
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPropiedades();
  }, []);

  useEffect(() => {
    if (estadoApp === "normal") setFiltrado(propiedades);
  }, [estadoApp]);

  const handleEliminar = async (id) => {
    try {
      const response = await fetch(`http://localhost/propiedades/${id}`, {
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

      fetchPropiedades();
      setElementId(null);
    } catch (e) {
      console.error("Error eliminando propiedad: ", e);
      setError(e);
    }
  };

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
        <h2 className="text-3xl font-semibold text-red-500">{error}</h2>
      </div>
    );
  }

  return (
    <section className="w-full inline-flex flex-col justify-center space-y-3 mt-16">
      <Boton tipo="agregar" onClick={() => setEstadoApp("agregando")} />
      {estadoApp === "agregando" && (
        <NewPropiedad
          actualizar={(m) => {
            if (m != null) {
              setFeedback(m);
              if (m.Status === "Success") {
                setEstadoApp("agregado");
                fetchPropiedades();
              } else setEstadoApp("fallido");
            } else setEstadoApp("normal");
          }}
          localidades={localidades}
          tipos_propiedad={tipoPropiedades}
        />
      )}
      <FiltroPropiedades
        localidades={localidades}
        tipos_propiedades={tipoPropiedades}
      />
      {(estadoApp === "editado" ||
        estadoApp === "agregado" ||
        estadoApp === "success" ||
        estadoApp === "fallido") && (
        <section
          className={estadoApp === "fallido" ? "bg-red-200" : "bg-green-200"}
        >
          <Boton tipo="cancelar" onClick={() => setEstadoApp("normal")}></Boton>
          <h3>{feedback.Status}</h3>
          <p>{feedback.Mensaje}</p>
        </section>
      )}
      <ul className="space-y-2">
        {filtrado.map((propiedad) => (
          <li
            key={propiedad.id}
            className="p-4 border border-gray-300 rounded-lg shadow-md flex flex-col space-y-2"
          >
            <h4 className="text-xl inline">{propiedad.id}.</h4>
            <h4 className="text-xl inline">
              {"Domicilio: " + propiedad.domicilio}.
            </h4>
            <h4 className="text-xl inline">
              {"Localidad: " + propiedad.localidad_id}.
            </h4>
            <h4 className="text-xl inline">
              {"Tipo de propiedad: " + propiedad.tipo_propiedad_id}.
            </h4>
            <h4 className="text-xl inline">
              {"Fecha de inicio de disponibilidad: " +
                propiedad.fecha_inicio_disponibilidad}
              .
            </h4>
            <h4 className="text-xl inline">
              {"Cantidad de huespedes: " + propiedad.cantidad_huespedes}.
            </h4>
            <h4 className="text-xl inline">
              {"Valor por noche: $" + propiedad.valor_noche}.
            </h4>
            <div className="inline-flex ml-auto space-x-4">
              {propiedad.id === elementId && estadoApp === "editando" ? (
                <EditPropiedad
                  propiedad={propiedad}
                  localidades={localidades}
                  tipos_propiedad={tipoPropiedades}
                  actualizar={(m) => {
                    if (m != null) {
                      setFeedback(m);
                      if (m.Status === "Success") {
                        setEstadoApp("editado");
                        fetchPropiedades();
                      } else setEstadoApp("fallido");
                    } else setEstadoApp("normal");
                  }}
                />
              ) : propiedad.id === elementId && estadoApp === "eliminando" ? (
                <Advertencia
                  elemento={
                    propiedad.domicilio + " de " + propiedad.localidad_id
                  }
                  onEstado={(estado) => {
                    if (estado === "confirmar") {
                      handleEliminar(propiedad.id);
                    } else setEstadoApp("normal");
                  }}
                />
              ) : (
                <>
                  <Boton
                    tipo="detalles"
                    onClick={() => {
                      setElementId(propiedad.id);
                      setEstadoApp("detalles");
                    }}
                  />
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
              )}
            </div>
          </li>
        ))}
      </ul>
      {estadoApp === "detalles" && (
        <DetailPropiedad
          propiedad={propiedades.find(
            (propiedad) => propiedad.id === elementId
          )}
          onEstado={() => setEstadoApp("normal")}
        />
      )}
    </section>
  );
}
