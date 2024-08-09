import React from "react";
import logo from "../assets/icons/logo.png";

export default function Header() {
  return (
    <header>
      <nav className="bg-gray-800 text-white p-4 shadow-nav-shadow fixed w-full">
        <div className="container mx-auto flex">
          <img src={logo} className="ml-auto max-w-8" alt="Inmobiliaria logo" />
        </div>
      </nav>
    </header>
  );
}
