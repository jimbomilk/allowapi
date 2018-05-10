@extends('layouts.default')
@section('content')

    <div class="container">
        <div class="row">

            <div class="bg-img">
                <img class="img-responsive" src="https://s-media-cache-ak0.pinimg.com/736x/08/b5/33/08b533f6a3142b797b44c0d046c9c8cf--arabian-decor-moroccan-interiors.jpg">

                <div class="caption-topmiddle padding margin black" style="border-radius: 10px; opacity: 0.8">
                    <p class="text-black">
                        Ha recibido esta imagen como responsable de los derechos de imagen de {{$name}}. El propietario de la imagen({{$owner}}) solicita su consentimiento para compartirla en las siguientes redes sociales:
                        @if ($sh[0]!=='0') #facebook @endif
                        @if ($sh[1]!=='0') #instagram @endif
                        @if ($sh[2]!=='0') #twitter @endif
                        @if ($sh[3]!=='0') #pagina web @endif
                        .Puede dar su consentimiento o denegarlo pulsando sobre una de las opciones.
                    </p>
                </div>

                <a href="{{$token}}/no">
                    <div class="caption-left padding margin red" style="border-radius: 10px; opacity: 0.8">
                        <h2 class="text-black">No</h2>
                    </div>
                </a>

                <a href="{{$token}}/si">
                    <div class="caption-right padding margin green" style="border-radius: 10px; opacity: 0.8">
                        <h2 class="text-black">Si</h2>
                    </div>
                </a>

                <div class="caption-bottommiddle padding margin black" style="border-radius: 10px; opacity: 0.8">
                    <p class="text-black">
                       Recuerde que tiene derecho a revocar este permiso accediendo a este <a href="#">enlace</a> en cualquier momento. Por favor, guárdelo o descargue la aplicación móvil Allowapp para gestionar sus derechos de imagen.
                    </p>
                </div>

            </div>

        </div>
    </div>

@stop