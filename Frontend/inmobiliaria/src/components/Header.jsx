import React from "react";

export default function Header() {
  // Si tengo el navbar no entiendo la funcion del header DUDA
  return (
    <header>
      <nav className="bg-gray-800 text-white p-4 shadow-nav-shadow fixed w-full">
        <div className="container mx-auto flex items-center justify-between">
          <button className="flex items-center text-white">Inmobiliaria</button>
          <img src="../../public/favicon.ico" alt="Inmobiliaria logo" />
        </div>
      </nav>
    </header>
  );
}
