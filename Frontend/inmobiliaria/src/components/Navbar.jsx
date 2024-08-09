import React, { useState } from "react";
import menuIcon from "../assets/icons/menu.png";

export default function Navbar() {
  const [isOpen, setIsOpen] = useState(false);

  const toggleSidebar = () => {
    setIsOpen(!isOpen);
  };

  return (
    <>
      {/* Botón de título para desplegar la barra lateral */}
      <button
        className="fixed bg-gray-800 text-white p-2 rounded-lg z-20 w-14"
        onClick={toggleSidebar}
      >
        <img src={menuIcon} alt="Menú desplegable" />
      </button>

      {/* Barra lateral */}
      <nav
        className={`bg-gray-800 text-white p-4 shadow-nav-shadow fixed left-0 top-0 h-full max-w-xs transition-transform transform ${isOpen ? "translate-x-0" : "-translate-x-full"}`}
      >
        <div className="container mx-auto flex flex-col items-start">
          <ul className="mt-20 space-y-4">
            <li className="nav-item">
              <button
                className="nav-button w-full text-left"
                onClick={toggleSidebar}
              >
                <a href="/localidades">Localidades</a>
              </button>
            </li>
            <li className="nav-item">
              <button
                className="nav-button w-full text-left"
                onClick={toggleSidebar}
              >
                <a href="/tipoPropiedad">Tipos de Propiedades</a>
              </button>
            </li>
            <li className="nav-item">
              <button
                className="nav-button w-full text-left"
                onClick={toggleSidebar}
              >
                <a href="/">Propiedades</a>
              </button>
            </li>
            <li className="nav-item">
              <button
                className="nav-button w-full text-left"
                onClick={toggleSidebar}
              >
                <a href="/inquilinos">Inquilinos</a>
              </button>
            </li>
            <li className="nav-item">
              <button
                className="nav-button w-full text-left"
                onClick={toggleSidebar}
              >
                <a href="/reservas">Reservas</a>
              </button>
            </li>
          </ul>
        </div>
      </nav>
    </>
  );
}
