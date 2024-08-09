import React, { useState, useEffect } from "react";
import Boton from "../../components/Boton";

const NewTipoPropiedad = ({ actualizar }) => {
  const [nombre, setNombre] = useState("");
  const [error, setError] = useState();
  const [mensaje, setMensaje] = useState(null);

  const handleAgregar = async (nombre) => {
    try {
      const response = await fetch("http://localhost/tipos_propiedad", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ nombre }), // Cambié la forma de enviar el nombre
      });
      const mensaje = await response.json();
      setMensaje(mensaje.Status); // Guardar el mensaje en el estado
      if (!response.ok) {
        setError(mensaje.Status + ": " + mensaje.Mensaje);
        throw new Error(`HTTP error! Error: ${JSON.stringify(mensaje)}`);
      } else actualizar(mensaje.Status);
    } catch (e) {
      console.error("Error agregando tipo de propiedad: ", e);
      setError(e.message);
    }
  };

  useEffect(() => {
    if (mensaje !== null) {
      actualizar(mensaje);
    }
  }, [mensaje, actualizar]);

  const validarForm = () => {
    let newError = {};
    const caracteres = (texto) => /^[A-Za-z]+$/.test(texto);

    if (!nombre.trim()) newError.nombre = "El campo no puede estar vacio.";
    else if (!caracteres(nombre))
      newError.nombre = "La propiedad no puede contener números.";

    setError(newError);
    return Object.keys(newError).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validarForm()) {
      handleAgregar(nombre);
      setNombre("");
    }
  };

  return (
    <div className="modal">
      <div className="modal-overlay"></div>
      <div className="modal-content shadow-nav-shadow">
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
            Nombre
            <input
              title="Nombre"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              pattern="[A-Za-z]+"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
            />
          </label>
          {error && <div className="bg-red-800 text-white">{error.nombre}</div>}

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
