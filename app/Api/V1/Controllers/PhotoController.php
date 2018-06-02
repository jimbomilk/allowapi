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
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Waavi\UrlShortener\Facades\UrlShortener;

class PhotoController extends Controller
{
    use Helpers;

    public function index($status)
    {
        $photos = array();
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (!$currentUser)
            throw new HttpException(500);

        // seleccionamos todos los links donde coincida el teléfono del usuario
        $rhs = RightholderPhoto::where('rhphone',$currentUser->phone)->get();

        foreach($rhs as $rh){
            if ($rh->photo && ($status == 'all' || $rh->status==$status))
                array_push($photos,$rh->photo);
        }

        return $photos;
    }

    private function image_path($phone){
        return public_path()."/img/users/".$phone."/";
    }
    public function store(PhotoRequest $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (!$currentUser)
            throw new HttpException(500);

        $data_origin = json_decode($request->get('data'));


        // Cogemos la imagen y la guardamos en un fichero
        File::makeDirectory( public_path().$this->image_path($currentUser->phone),0755,true,true);
        $time = time();
        $photo_path_original = "original-".$time.".png";
        $photo_path_pending = "pending-".$time.".png";
        $img = Image::make(file_get_contents($data_origin->src));
        $img_pending = Image::make(file_get_contents($data_origin->src));

        $img_pending->resize(null,600, function ($constraint) {
            $constraint->aspectRatio();
        });

        $watermark_pending = Image::make(public_path().'/img/watermark_pendiente.png');
        $img_pending->insert($watermark_pending, 'top-left');
        $img_pending->insert($watermark_pending, 'center');
        $img_pending->insert($watermark_pending, 'bottom-right');

        $img_pending->save($this->image_path($currentUser->phone).$photo_path_pending);
        $img->save($this->image_path($currentUser->phone).$photo_path_original);

        if(!$img)
            return $this->response->error('no se pudo guardar la foto', 500);

        $data_origin->remoteSrc = $photo_path_pending;
        $data_origin->src = $photo_path_original;

        if ($data_origin->remoteId == -1)
            $photo = new Photo();
        else
            $photo = $currentUser->photos()->find($data_origin->remoteId);

        if (!$photo)
            throw new HttpException(500);

        $photo->data = json_encode($data_origin);

        if($res = $currentUser->photos()->save($photo))
            return response()->json(['photoId' => $res->id,'path'=>$photo_path_pending]);
        else
            return $this->response->error('no se pudo salvar la foto', 500);
    }

    public function setSharing($obj){


        $sh = ($obj->sharing->facebook=="")?"0":"1";
        $sh .= ($obj->sharing->twitter=="")?"0":"1";
        $sh .= ($obj->sharing->instagram=="")?"0":"1";
        $sh .= ($obj->sharing->web=="")?"0":"1";
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
                            $rhPhoto->status = 0;
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
        if (!$currentUser)
            return $this->response->error('Usuario no verificado', 500);

        $photo = $currentUser->photos()->find($id);

        // Ahora buscamos en las fotos en las que es rightholder
        $rh = RightholderPhoto::where('photo_id','=',$id)
            ->where('rhphone','=',$currentUser->phone)
            ->first();
        if(!$photo) {
            if (!$rh)
                return $this->response->error('No hay ningún responsable de esa foto', 500);
            else{
                $photo = $rh->photo;
            }
        }


        // extraemos ambos json
        $initialData = json_decode($photo->data,true);
        $finalData = json_decode($request->get('data'),true);



        //asignamos valores
        $initialData['status'] = $finalData['status'];
        $initialData['sharing'] = $finalData['sharing'];
        $initialData['people'] = $finalData['people'];
        $initialData['log'] = $finalData['log'];

        //empaquetamos y guardamos
        $photo->data = json_encode($initialData);

        if($photo->save()) {
            if ($rh) {
                $rh->status = $finalData['status'];
                $rh->save();
            }
            return response()->json(['ok']);
        }
        else
            return $this->response->error('could_not_update_photo', 500);
    }

    public function destroy($id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (!$currentUser)
            return $this->response->error('Usuario no verificado', 500);
        $photo = $currentUser->photos()->find($id);

        if(!$photo)
            throw new NotFoundHttpException;

        $photo_data = json_decode($photo->data);
        if($photo->delete()) {
            $original = str_replace("pending","original",$photo_data->src);
            File::delete($this->image_path($currentUser->phone).$photo_data->src);
            File::delete($this->image_path($currentUser->phone).$original);

            return $this->response->noContent();
        }
        else
            return $this->response->error('could_not_delete_photo', 500);
    }

    public function storage($filename){
        $currentUser = JWTAuth::parseToken()->authenticate();
        if (!$currentUser)
            return $this->response->error('Usuario no verificado', 500);
        return Image::make($this->image_path($currentUser->phone) . $filename)->response();
//return $this->response->error($this->image_path($currentUser->phone).$filename,200);
    }
}
