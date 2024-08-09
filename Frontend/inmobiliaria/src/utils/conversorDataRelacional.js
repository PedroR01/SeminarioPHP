import _ from "lodash";

export default async function conversorDataRelacional(
  mainData,
  ...dataRelacional
) {
  let data = _.cloneDeep(mainData); // Clonar los datos para evitar modificar los originales

  // Función auxiliar para procesar inquilinos
  const fetchInquilinos = async () => {
    try {
      const response = await fetch("http://localhost/inquilinos");
      if (!response.ok) {
        const error = await response.json();
        console.dir(error);
        throw new Error(`HTTP error! Error: ${JSON.stringify(error)}`);
      }

      const responseInquilinos = await response.json();
      data = data.map((d) => {
        const inquilino = responseInquilinos["Data"].find(
          (i) => i.id === d.inquilino_id
        );
        if (inquilino) {
          d.inquilino_id = `${inquilino.nombre} ${inquilino.apellido}`;
        }
        return d;
      });
    } catch (e) {
      console.error("Error obteniendo inquilinos: ", e);
      console.dir(e);
    }
  };

  // Función auxiliar para procesar propiedades
  const fetchPropiedades = async () => {
    try {
      const response = await fetch("http://localhost/propiedades");
      if (!response.ok) {
        const error = await response.json();
        console.dir(error);
        throw new Error(`HTTP error! Error: ${JSON.stringify(error)}`);
      }

      const responsePropiedades = await response.json();
      data = data.map((d) => {
        const propiedad = responsePropiedades["Data"].find(
          (p) => p.id === d.propiedad_id
        );
        if (propiedad) {
          d.propiedad_id = propiedad.domicilio;
        }
        return d;
      });
    } catch (e) {
      console.error("Error obteniendo propiedades: ", e);
      console.dir(e);
    }
  };

  // Ejecutar las operaciones según los parámetros pasados
  const promises = [];
  if (dataRelacional.includes("inquilinos")) promises.push(fetchInquilinos());
  if (dataRelacional.includes("propiedades")) promises.push(fetchPropiedades());

  await Promise.all(promises); // Espera a que se completen todas las promesas antes de retornar el resultado.

  return data;
}
