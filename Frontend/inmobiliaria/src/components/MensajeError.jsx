import React from "react";
import Boton from "./Boton";

export default function MensajeError({ err, actualizar }) {
  return (
    <section className="bg-red-200 p-2 rounded-md mb-3">
      <div className="flex items-center justify-between">
        <h4 className="font-semibold">Solicitud fallida {err.Codigo}</h4>
        {console.log(err)}
        <Boton
          className="ml-auto"
          tipo="cancelar"
          onClick={() => actualizar()}
        />
      </div>
      <p>{err.Mensaje}</p>
      <p>{err.Data}</p>
    </section>
  );
}
