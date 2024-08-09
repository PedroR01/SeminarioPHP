export default async function obtenerTabla(tabla) {
  let data = [];
  const fetchPropiedades = async () => {
    try {
      const response = await fetch("http://localhost/propiedades");
      const mensaje = await response.json();
      if (!response.ok) {
        console.dir(mensaje);
        //  throw new Error(`HTTP error! Error: ${JSON.stringify(mensaje)}`);
      }
      data = mensaje["Data"];
    } catch (e) {
      console.error("Error obteniendo propiedades: ", e);
      console.dir(e);
    }
  };

  const fetchInquilinos = async () => {
    try {
      const response = await fetch("http://localhost/inquilinos");
      const mensaje = await response.json();
      if (!response.ok) {
        console.dir(mensaje);
        //throw new Error(`HTTP error! Error: ${JSON.stringify(mensaje)}`);
      }

      data = mensaje["Data"];
    } catch (e) {
      console.error("Error obteniendo inquilinos: ", e);
      console.dir(e);
    }
  };

  if (tabla === "propiedades") await fetchPropiedades();
  else if (tabla === "inquilinos") await fetchInquilinos();

  return data;
}
