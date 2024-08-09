import React, { useState } from "react";

export default function FiltroPropiedades({ localidades, tipos_propiedades }) {
  const [filtros, setFiltros] = useState({
    disponible: "",
    fecha_inicio_disponibilidad: "",
    localidad_id: "",
    cantidad_huespedes: "",
  });
  const [propiedades, setPropiedades] = useState([]);
  const [error, setError] = useState("");

  const handleInput = (e) => {
    const { name, value } = e.target;
    setFiltros({ ...filtros, [name]: value });
  };

  const limpiarFiltros = () => {
    setFiltros({
      disponible: "",
      fecha_inicio_disponibilidad: "",
      localidad_id: "",
      cantidad_huespedes: "",
    });
  };

  const fetchPropiedades = async () => {
    try {
      let query = Object.keys(filtros)
        .map((key) => {
          if (filtros[key] !== "") {
            return `${key}=${encodeURIComponent(filtros[key])}`;
          }
          return null;
        })
        .filter((item) => item !== null)
        .join("&");

      const response = await fetch(`http://localhost/propiedades?${query}`);
      const data = await response.json();

      if (response.ok) {
        setPropiedades(data.Data);
        setError("");
      } else {
        setError(data.Mensaje);
        setPropiedades([]);
      }
    } catch (error) {
      setError("Error fetching propiedades.");
    }
  };

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold mb-6">Propiedades</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <label className="block">
          <span className="text-gray-700">Disponible:</span>
          <select
            name="disponible"
            value={filtros.disponible}
            onChange={handleInput}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          >
            <option value="">Cualquiera</option>
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>
        </label>
        <label className="block">
          <span className="text-gray-700">
            Fecha de Inicio de Disponibilidad:
          </span>
          <input
            type="date"
            name="fecha_inicio_disponibilidad"
            value={filtros.fecha_inicio_disponibilidad}
            onChange={handleInput}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
        <label className="block">
          <span className="text-gray-700">Localidad:</span>
          <select
            type="number"
            name="localidad_id"
            value={filtros.localidad_id}
            onChange={handleInput}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          >
            <option disabled value="">
              Seleccione una localidad
            </option>
            {localidades.map((localidad) => (
              <option className="ml-3" key={localidad.id} value={localidad.id}>
                {localidad.nombre}
              </option>
            ))}
          </select>
        </label>
        <label className="block">
          <span className="text-gray-700">Cantidad de Huéspedes:</span>
          <input
            type="number"
            name="cantidad_huespedes"
            value={filtros.cantidad_huespedes}
            onChange={handleInput}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </label>
      </div>
      <div className="flex space-x-4 mb-6">
        <button
          onClick={fetchPropiedades}
          className="bg-gray-700 text-white px-4 py-2 rounded-md shadow-sm hover:bg-gray-800 shadow-nav-shadow"
        >
          Buscar
        </button>
        <button
          onClick={limpiarFiltros}
          className="text-black px-4 py-2 rounded-md shadow-sm hover:bg-gray-200 shadow-nav-shadow"
        >
          Limpiar Filtros
        </button>
      </div>
      {error && <div className="text-red-500 mb-4">{error}</div>}
      <div className="grid grid-cols-1 gap-4">
        {propiedades.length > 0
          ? propiedades.map((propiedad) => (
              <div
                key={propiedad.id}
                className="p-4 border rounded-lg shadow-sm"
              >
                <p className="font-semibold">{propiedad.domicilio}</p>
                <p>
                  Localidad:{" "}
                  {
                    localidades.find(
                      (localidad) => localidad.id === propiedad.localidad_id
                    ).nombre
                  }
                </p>
                <p>Disponible: {propiedad.disponible ? "Sí" : "No"}</p>
                <p>
                  Fecha de Disponibilidad:{" "}
                  {propiedad.fecha_inicio_disponibilidad}
                </p>
                <p>Cantidad de Huéspedes: {propiedad.cantidad_huespedes}</p>
                <p>
                  Tipo de Propiedad:{" "}
                  {
                    tipos_propiedades.find(
                      (tipo) => tipo.id === propiedad.tipo_propiedad_id
                    ).nombre
                  }
                </p>
                <p>Valor por Noche: ${propiedad.valor_noche}</p>
              </div>
            ))
          : null}
      </div>
    </div>
  );
}
