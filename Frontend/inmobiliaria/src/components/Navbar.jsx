// DUDA: Nose como quieren que hagamos el Header y el navbar, yo lo dejo asi para por lo menos poder probar las pags
import React, { useState } from "react";

export default function Navbar() {
  const [isOpen, setIsOpen] = useState(false);

  const toggleSidebar = () => {
    setIsOpen(!isOpen);
  };

  return (
    <>
      {/* Botón de título para desplegar la barra lateral */}
      <button
        className="fixed top-4 left-4 bg-gray-800 text-white p-2 rounded-lg z-20"
        onClick={toggleSidebar}
      >
        Menú
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
                <a href="/propiedades">Propiedades</a>
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

const renderPage = (e) => {
  console.log(e.target.innerHTML.toLowerCase());
};
