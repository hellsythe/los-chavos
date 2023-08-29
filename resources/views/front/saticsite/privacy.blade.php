@extends('front.layouts.app')

@section('content')
<div>
    <div class="relative">
        <div class="min-h-screen py-6 flex flex-col justify-center sm:py-12 z-10 relative">
            <div class="relative py-3 sm:max-w-xl sm:mx-auto">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-light-blue-500 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl">
                </div>
                <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">
                    <div class="max-w-md mx-auto">
                        <div class="flex flex-wrap content-center justify-center">
                            <img src="{{ asset('img/logo.svg') }}" class="h-24" />
                        </div>
                        <div class="divide-y divide-gray-200">
                            <strong><h1>Aviso de Privacidad</h1></strong> </br>

                            <strong><h1>Fecha de última actualización: 29 de agosto de 2023</h1></strong> </br>

                            <strong><h1>1. Responsable de la protección de datos personales</h1></strong> </br>

                            Nombre de la entidad responsable: Taller los chavos </br>
                            Domicilio: Juárez esquina Pedro García, C.P. 95100, Tierra Blanca, Veracruz, México </br>
                            Teléfono de contacto: 2747460122 </br>
                            Correo electrónico de contacto: ravi_loschavos@hotmail.com </br></br>

                            <strong> <h1> Información recopilada</h1></strong> </br>

                            Recopilaremos los siguientes tipos de datos personales: </br>

                            Nombre y apellidos </br>
                            Correo electrónico </br>
                            Número de teléfono </br> </br>

                            <strong> <h1>3. Finalidad del tratamiento de datos personales</h1></strong> </br>

                            Los datos personales recopilados serán utilizados para las siguientes finalidades: </br>

                            Contactar a los usuarios para confirmar citas, recordatorios y atención al cliente. </br>
                            Enviar información promocional sobre nuestros servicios y ofertas especiales. </br></br>

                            <strong> <h1>4. Consentimiento</h1></strong> </br>

                            Al proporcionar tus datos personales, estás aceptando los términos y condiciones de este aviso de privacidad. </br></br>

                            <strong> <h1>5. Derechos ARCO</h1></strong> </br>

                            Los titulares de datos personales tienen los derechos de Acceso, Rectificación, Cancelación y Oposición (ARCO) en relación con sus datos. Para ejercer estos derechos, podrán presentar una solicitud por escrito a través de los siguientes medios: </br>

                            Teléfono: 2747460122 </br>
                            Correo electrónico: ravi_loschavos@hotmail.com </br></br>

                            <strong> <h1>6. Medidas de seguridad</h1></strong> </br>
                            Hemos implementado medidas de seguridad técnicas, administrativas y físicas para proteger los datos personales contra pérdida, robo, acceso no autorizado, divulgación, alteración o destrucción. </br></br>

                            <strong> <h1>7. Cambios al aviso de privacidad</h1></strong> </br>

                            Este aviso de privacidad podría ser actualizado ocasionalmente para reflejar cambios en nuestras prácticas de manejo de datos personales. Se publicará una versión actualizada en nuestro sitio web o se proporcionará de otra manera en caso necesario. </br></br>

                            <strong> <h1>8. Contacto</h1></strong> </br>

                            Si tienes alguna pregunta, solicitud o comentario relacionado con este aviso de privacidad, por favor comunícate con nosotros a través de los siguientes medios: </br></br>

                            Teléfono: 2747460122 </br>
                            Correo electrónico: ravi_loschavos@hotmail.com </br>
                            Domicilio: Juárez esquina Pedro García, C.P. 95100, Tierra Blanca, Veracruz, México </br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="area absolute top-0 z-0">
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>
    </div>
@endsection
