<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\PhotoRequest;
use App\Response;
use App\RightholderPhoto;
use App\Token;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use App\Photo;
use Illuminate\Foundation\Testing\HttpException;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Waavi\UrlShortener\Facades\UrlShortener;

class PhotoController extends Controller
{
    use Helpers;

    public function index()
    {
        $photos = array();
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (!$currentUser)
            throw new HttpException(500);


        $rhs = RightholderPhoto::where('rhphone',$currentUser->phone)->get();

        foreach($rhs as $rh){
            array_push($photos,$rh->photo);
        }

        return $photos;
    }

    public function store(PhotoRequest $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        if (!$currentUser)
            throw new HttpException(500);

        $photo = new Photo();

        $photo->data = $request->get('data');
        if (!$currentUser->photos())
            throw new HttpException(500);

        if($res = $currentUser->photos()->save($photo))
            return response()->json(['photoId' => $res->id]);
        else
            return $this->response->error('could_not_create_photo', 500);
    }

    public function setSharing($obj){
        if (!property_exists($obj,'sharing'))
            return "0000";
        $sh = ($obj->sharing->facebook)?"1":"0";
        $sh .= ($obj->sharing->twitter)?"1":"0";
        $sh .= ($obj->sharing->instagram)?"1":"0";
        $sh .= ($obj->sharing->web)?"1":"0";
        return $sh;
    }

    /* Retorna los links de los rightholders */
    public function show($id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (!$currentUser)
            throw new HttpException(500);

        $photo = $currentUser->photos()->find($id);
        if(!$photo)
            throw new NotFoundHttpException;

        // Recoge el data y lo vuelca en un objeto
        $obj = json_decode($photo->data);
        $tokens = array();
        if (!property_exists($obj,'people'))
            return response()->json($tokens,204); // 204 - no content

        // Borramos los enlaces que hubiera antes
        RightholderPhoto::where('photo_id',$id)->delete();

        foreach ($obj->people as $person){

            if (property_exists ($person,'rightholders')){

                foreach ($person->rightholders as $rh) {
                    if ($rh) {
                        $sh = $this->setSharing($obj);


                        if ($sh !== '0000') {
                            $route = route('photo.link', ['id' => $id, 'owner' => $obj->owner, 'name' => $person->name, 'phone' => $person->phone, 'rhname' => $rh->name, 'rhphone' => $rh->phone, 'sharing' => $sh, 'token' => Token::generate($id, $obj->owner, $person->name, $person->phone, $rh->name, $rh->phone)]);
                            $route=UrlShortener::shorten($route);


                            $rhPhoto = new RightholderPhoto();
                            $rhPhoto->photo_id = $id;
                            $rhPhoto->owner= $obj->owner;
                            $rhPhoto->name= $person->name;
                            $rhPhoto->phone= $person->phone;
                            $rhPhoto->rhname= $rh->name;
                            $rhPhoto->rhphone= $rh->phone;
                            $rhPhoto->sharing = json_encode($sh);
                            $rhPhoto->link = $route;
                            $rhPhoto->save();

                            array_push($tokens, ['owner' => $obj->owner, 'name' => $person->name, 'phone' => $person->phone, 'rhname' => $rh->name, 'rhphone' => $rh->phone, 'link' => $route]);
                        }
                    }
                }
            }

        }

        return response()->json($tokens,200);
    }

    public function update(PhotoRequest $request, $id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $photo = $currentUser->photos()->find($id);

        // Ahora buscamos en las fotos en las que es rightholder
        $rh = RightholderPhoto::where('photo_id','=',$id)->where('rhphone','=',$currentUser->$phone)->first();
        if(!$photo ) {
            if (!$rh)
                throw new NotFoundHttpException;
            else{
                $photo = $rh->photo;
            }
        }

        // extraemos ambos json
        $initialData = json_decode($photo->data);
        $finalData = json_decode($request->get('data'));

        //asignamos valores
        $initialData->status = $finalData->status;
        $initialData->sharing = $finalData->sharing;
        $initialData->people = $finalData->people;
        $initialData->log = $finalData->log;

        //empaquetamos y guardamos
        $photo->data = json_encode($initialData);

        if($photo->save())
            return response()->json($photo->data);
        else
            return $this->response->error('could_not_update_photo', 500);
    }

    public function destroy($id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $photo = $currentUser->photos()->find($id);

        if(!$photo)
            throw new NotFoundHttpException;

        if($photo->delete())
            return $this->response->noContent();
        else
            return $this->response->error('could_not_delete_photo', 500);
    }
}
