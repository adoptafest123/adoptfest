import Navbar from '../components/Navbar';
import Hero from '../components/Hero';
import MascotasDestacadas from '../components/MascotasDestacadas';
import EventosDestacados from '../components/EventosDestacados';
import Footer from '../components/Footer';

function Inicio() {
  return (
    <>
      <Navbar />
      <Hero />
      <MascotasDestacadas />
      <EventosDestacados />
      <Footer />
    </>
  );
}

export default Inicio;