import React from "react";
import Boton from "../../components/Boton";

// Falta añadirle funcionalidad para eliminar y editar. RESOLVER
export default function DetailPropiedad({ propiedad, onEstado }) {
  return (
    <div className="modal">
      <div className="modal-overlay"></div>
      <section className="modal-content shadow-nav-shadow">
        <div className="w-full inline-flex flex-row">
          <Boton className="mb-6" tipo={"cancelar"} onClick={onEstado} />
          <div className="w-full inline-flex flex-row-reverse space-x-4">
            <Boton className="mb-6" tipo={"eliminar"} onClick={onEstado} />
            <Boton className="mb-6" tipo={"editar"} onClick={onEstado} />
          </div>
        </div>
        <div className="inline-flex flex-col text-white">
          <h4 className="text-xl inline">
            {"ID de la propiedad: " + propiedad.id}.
          </h4>
          <h4 className="text-xl inline">
            {"Localidad: " + propiedad.localidad_id}.
          </h4>
          <h4 className="text-xl inline">
            {"Domicilio: " + propiedad.domicilio}.
          </h4>
          <h4 className="text-xl inline">
            {"Tipo de propiedad: " + propiedad.tipo_propiedad_id}.
          </h4>
          <h4 className="text-xl inline">
            {"Disponible: " + (propiedad.disponible === 1 ? "Sí" : "No")}.
          </h4>
          <h4 className="text-xl inline">
            {"Valor por noche: $" + propiedad.valor_noche}.
          </h4>
          <h4 className="text-xl inline">
            {"Fecha de inicio de disponibilidad: " +
              propiedad.fecha_inicio_disponibilidad}
            .
          </h4>
          <h4 className="text-xl inline">
            {"Cantidad de dias: " + propiedad.cantidad_dias}.
          </h4>
          <h4 className="text-xl inline">
            {"Cantidad de huespedes: " + propiedad.cantidad_huespedes}.
          </h4>
          <h4 className="text-xl inline">
            {"Cantidad de habitaciones: " +
              (propiedad.cantidad_habitaciones == null
                ? "Sin especificar."
                : propiedad.cantidad_habitaciones)}
          </h4>
          <h4 className="text-xl inline">
            {"Cantidad de baños: " +
              (propiedad.cantidad_banios == null
                ? "Sin especificar."
                : propiedad.cantidad_banios)}
            .
          </h4>
          <h4 className="text-xl inline">
            {"Cochera: " + (propiedad.cochera === 1 ? "Sí" : "No")}.
          </h4>
          {propiedad.imagen && (
            <h4 className="text-xl inline">
              {"Imagen: "}
              <img
                src={`data:image/${propiedad.tipo_imagen};base64,${propiedad.imagen}`}
                alt="Imagen de la propiedad"
              />
            </h4>
          )}
        </div>
      </section>
    </div>
  );
}
