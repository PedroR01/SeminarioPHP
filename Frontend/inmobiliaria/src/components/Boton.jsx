import React from "react";
import editarIcon from "../assets/icons/editar.png";
import eliminarIcon from "../assets/icons/eliminar.png";
import confirmarIcon from "../assets/icons/confirmar.png";
import agregarIcon from "../assets/icons/agregar.png";
import cancelarIcon from "../assets/icons/cancelar.png";
import detallesIcon from "../assets/icons/detalles.png";

// Es lo mismo export default function que esta forma de aca? DUDA
const Boton = ({ tipo, onClick, submit }) => {
  const iconMap = {
    editar: editarIcon,
    eliminar: eliminarIcon,
    confirmar: confirmarIcon,
    agregar: agregarIcon,
    cancelar: cancelarIcon,
    detalles: detallesIcon,
  };

  if (submit)
    return (
      <button
        className="inline btn shadow-nav-shadow"
        onClick={onClick}
        type="submit"
      >
        <img className="max-w-6" src={iconMap[tipo]} alt={tipo} />
      </button>
    );
  else
    return (
      <button
        className="inline btn shadow-nav-shadow"
        onClick={onClick}
        type="button"
      >
        <img className="max-w-6" src={iconMap[tipo]} alt={tipo} />
      </button>
    );
};

export default Boton;
