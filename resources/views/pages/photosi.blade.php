@extends('layouts.default')
@section('content')

    <div class="container">
        <div class="row">

            <div class="bg-img">
                <img class="img-responsive" src="https://s-media-cache-ak0.pinimg.com/736x/08/b5/33/08b533f6a3142b797b44c0d046c9c8cf--arabian-decor-moroccan-interiors.jpg">

                <div class="caption-topmiddle padding margin black" style="border-radius: 10px; opacity: 0.8">
                    <p class="text-black">
                        Ha <strong>concedido</strong> su permiso a {{$owner}} para que comparta esta imagen en
                        @if ($sh[0]!=='0') #facebook @endif
                        @if ($sh[1]!=='0') #instagram @endif
                        @if ($sh[2]!=='0') #twitter @endif
                        @if ($sh[3]!=='0') #pagina web @endif. ¡Muchas gracias!
                    </p>
                </div>



                <div class="caption-bottommiddle padding margin black" style="border-radius: 10px; opacity: 0.8">
                    <p class="text-black">
                        Recuerde que tiene el derecho a revocar este permiso accediendo a este <a href="{{$link}}">enlace</a> en cualquier momento.
                    </p>
                </div>

            </div>

        </div>
    </div>

@stop