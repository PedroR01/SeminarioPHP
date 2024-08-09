import React, { useEffect, useState } from "react";
import Boton from "../../components/Boton";

const EditTipoPropiedad = ({ propiedad, actualizar }) => {
  const [respaldo, setRespaldo] = useState("");
  const [nombre, setNombre] = useState(propiedad.nombre);
  const [error, setError] = useState();

  useEffect(() => {
    setRespaldo(propiedad.nombre);
  }, []);

  const handleEditar = async (id) => {
    try {
      const response = await fetch(`http://localhost/tipos_propiedad/${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ nombre }),
      });
      const mensaje = await response.json();
      console.dir(mensaje);
      if (!response.ok) {
        setError(mensaje.Status + ": " + mensaje.Mensaje);
        throw new Error(`HTTP error! Error: ${mensaje.Mensaje}`);
      } else actualizar(mensaje.Status); // NOSE PORQUE ACA NO ME DEJA MANDAR EL MENSAJE COMO OBJETO COMPLETO PERO PARA LA RESERVA SI. Duda
    } catch (e) {
      setError("Error editando propiedad: " + e);
    }
  };

  const validar = () => {
    let newError = "";
    const caracteres = (texto) => /^[A-Za-z]+$/.test(texto);

    if (!nombre.trim()) newError = "El campo no puede estar vacio.";
    else if (!caracteres(nombre))
      newError = "La propiedad no puede contener números.";

    setError(newError);
    return newError === "";
  };

  const cambiarNombre = (e) => {
    setNombre(e.target.value);
  };

  const validacion = (e) => {
    e.preventDefault();
    if (validar()) {
      setNombre(e.target[0].value);
      handleEditar(propiedad.id);
    }
  };

  return (
    <>
      <form onSubmit={validacion}>
        <input
          type="text"
          defaultValue={nombre}
          onChange={(e) => cambiarNombre(e)}
        />
        {error && <div className="bg-red-800 text-white">{error}</div>}
        <div className="inline-flex ml-auto space-x-4">
          <Boton tipo="confirmar" submit={true} />
          <Boton
            tipo="cancelar"
            onClick={() => {
              actualizar(null);
              propiedad.nombre = respaldo;
            }}
          />
        </div>
      </form>
    </>
  );
};

export default EditTipoPropiedad;
