import React, { useEffect, useState } from "react";
import Boton from "../../components/Boton";

export default function TipoPropiedadPage() {
  const [propiedades, setPropiedades] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [nombre, setNombre] = useState("");

  const estado = {
    NORMAL: "normal",
    EDITANDO: "editando",
    BORRANDO: "borrando",
    AGREGANDO: "agregando",
    ENVIANDO: "enviando",
  };
  const [estadoApp, setEstadoApp] = useState(estado.NORMAL);

  useEffect(() => {
    if (estadoApp === "normal") {
      const fetchPropiedades = async () => {
        try {
          const response = await fetch("http://localhost/tipos_propiedad");
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          const propiedad = await response.json();
          setPropiedades(propiedad["Data"]);
          setLoading(false);
        } catch (e) {
          setError(e.message);
          setLoading(false);
        }
      };

      fetchPropiedades();
    }

    if (estadoApp === "enviando") {
      const fetchPropiedades = async () => {
        try {
          const response = await fetch("http://localhost/tipos_propiedad", {
            method: "POST",
            body: JSON.stringify(nombre),
          });
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          const propiedad = await response.json();
          setPropiedades(propiedad["Data"]);
          setLoading(false);
        } catch (e) {
          setError(e.message);
          setLoading(false);
        }
      };
      fetchPropiedades();
    }

    if (estadoApp === "editando") {
      setNombre(nombre[0].nombre);
      console.log("editando");
    }
  }, [estadoApp]);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  const cambiarNombre = (e) => {
    setNombre(e.target.value);
  };

  return (
    <>
      <section className="listado container flex justify-center items-center">
        <ul>
          {propiedades.map((propiedad) => (
            <li
              key={propiedad.id}
              className="p-4 min-w-96 border border-gray-300 rounded-lg shadow-md flex"
            >
              <h4 className="text-xl inline">{propiedad.id + "."}</h4>
              {/*Tengo que mostrar el id de la base de datos o lo muestro con un ID más organizado en el front? DUDA*/}
              {estadoApp === "editando" ? (
                <input
                  type="text"
                  value={nombre}
                  onChange={cambiarNombre}
                ></input>
              ) : (
                <h4 className="text-xl font-semibold inline ">
                  {propiedad.nombre}
                </h4>
              )}

              <div className="inline-flex ml-auto  space-x-4">
                {/* Pasar el estado de la app como contexto para cambiarlo con el boton */}
                <Boton tipo="editar" />
                <Boton tipo="eliminar" />
              </div>
            </li>
          ))}
        </ul>
      </section>
    </>
  );
}
