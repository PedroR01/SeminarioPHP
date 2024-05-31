import React from "react";
import editarIcon from "../assets/icons/editar.png";
import eliminarIcon from "../assets/icons/eliminar.png";
import confirmarIcon from "../assets/icons/confirmar.png";

export default function Boton({ tipo }) {
  switch (tipo) {
    case "editar":
      return (
        <button
          className="bg-gray-200 p-2 inline rounded-lg shadow-nav-shadow"
          onClick={editar}
        >
          <img className="max-w-6" src={editarIcon} alt="a" />
        </button>
      );

    case "eliminar":
      return (
        <button
          className="bg-gray-200 p-2 inline rounded-full shadow-nav-shadow"
          onClick={eliminar}
        >
          <img className="max-w-6" src={eliminarIcon} alt="b" />
        </button>
      );
    case "confirmar":
      return (
        <button className="" onClick={confirmar}>
          <img src={confirmarIcon} alt="c" />
        </button>
      );
  }

  return alert("Tipo de boton no especificado.");
}

const editar = (e) => {
  console.log(e.target);
  console.log("editado");
};

const eliminar = () => {
  console.log("eliminado");
};

const confirmar = () => {
  console.log("confirmado");
};
