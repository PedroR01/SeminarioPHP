import React from "react";
import Boton from "./Boton";

export default function Advertencia({ elemento, onEstado }) {
  return (
    <section className="bg-yellow-200 p-2 rounded-md ml-3">
      <div className="inline-flex space-x-3">
        <h4 className="font-bold">
          ¿Esta seguro que desea eliminar {elemento}?
        </h4>
        <Boton tipo="confirmar" onClick={() => onEstado("confirmar")} />
        <Boton tipo="cancelar" onClick={() => onEstado("cancelar")} />
      </div>
    </section>
  );
}
