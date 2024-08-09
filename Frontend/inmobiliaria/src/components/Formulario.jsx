import React, { useState } from "react";
import Boton from "./Boton";

export default function Formulario({ tipo, onEstado, data }) {
  // Estas no van. Es para que no me tire error
  const [propiedad, setPropiedad] = useState();
  const localidades = data;
  //

  const handleSubmit = () => {};
  return (
    <div className="modal">
      <div className="modal-overlay"></div>
      <div className="modal-content shadow-nav-shadow h-4/5 overflow-y-scroll">
        <Boton className="mb-6" tipo={"cancelar"} onClick={onEstado} />
        <form
          className="flex flex-col space-y-4 w-full"
          onSubmit={handleSubmit}
        >
          <label className="text-zinc-200 font-semibold input-title">
            Domicilio:
            <input
              title="Domicilio"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              defaultValue={propiedad.domicilio}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Localidad:
            <select
              title="Localidad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              defaultValue={propiedad.localidad_id}
              onChange={(e) => setPropiedad(e.target.value)}
            >
              <option disabled value="">
                Seleccione una localidad
              </option>
              {localidades.map((localidad) => (
                <option
                  className="ml-3"
                  key={localidad.id}
                  value={localidad.nombre}
                >
                  {localidad.nombre}
                </option>
              ))}
            </select>
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de habitaciones:
            <input
              title="Cantidad de habitaciones"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_habitaciones}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de baños:
            <input
              title="Cantidad de baños"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_banios}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Cochera:
            <input
              title="Cochera"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="checkbox"
              defaultValue={propiedad.cochera}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de huespedes:
            <input
              title="Cantidad de huespedes"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_huespedes}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Fecha de inicio de disponibilidad:
            <input
              title="Fecha de inicio de disponibilidad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="date"
              defaultValue={propiedad.fecha_inicio_disponibilidad}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Cantidad de días:
            <input
              title="Cantidad de días"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.cantidad_dias}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Valor por noche:
            <input
              title="Valor por noche"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="number"
              defaultValue={propiedad.valor_noche}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Tipo de propiedad:
            <input
              title="Tipo de propiedad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="text"
              defaultValue={propiedad.tipo_propiedad_id}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <label className="text-zinc-200 font-semibold input-title">
            Imagen de la propiedad:
            <input
              title="Imagen de la propiedad"
              className="bg-slate-800 text-slate-200 rounded-lg p-3"
              type="file"
              defaultValue={propiedad.imagen}
              onChange={(e) => setPropiedad(e.target.value)}
            />
          </label>
          <button
            className="btn shadow-nav-shadow w-1/2 self-center"
            type="submit"
          >
            Agregar
          </button>
        </form>
      </div>
    </div>
  );
}
