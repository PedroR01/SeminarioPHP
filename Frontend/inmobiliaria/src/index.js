import React from "react";
import ReactDOM from "react-dom/client";
import { createBrowserRouter, Router, RouterProvider } from "react-router-dom";
import "./index.css";
import reportWebVitals from "./reportWebVitals";
import Header from "./components/Header";
import Navbar from "./components/Navbar";
import Footer from "./components/Footer";
import LocalidadPage from "./pages/localidad/LocalidadPage";
import TipoPropiedadPage from "./pages/tipoPropiedad/TipoPropiedadPage";
import ErrorPage from "./pages/ErrorPage";

const error = () => (
  <>
    <Header />
    <Navbar />
    <ErrorPage />
    <Footer />
  </>
);

const router = createBrowserRouter([
  {
    //localHost main
    path: "/",
    element: (
      <>
        <Header />
        <Navbar />
        <Footer />
      </>
    ),
    errorElement: error(),
  },
  {
    //Localidades
    path: "/localidades",
    element: <LocalidadPage />,
    errorElement: error(),
  },
  {
    //tipoPropiedad
    path: "/tipoPropiedad",
    element: <TipoPropiedadPage />,
    errorElement: error(),
  },
]);

const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
