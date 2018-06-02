<?php

namespace App\Http\Controllers;

use App\Photo;
use App\Token;
use Illuminate\Http\Request;

class LinkController extends Controller
{

    public function link($photoId,$owner,$name,$phone,$rhname,$rhphone,$sharing,$token)
    {
        $resToken = Token::generate($photoId,$owner,$name,$phone,$rhname,$rhphone);

        if (hash_equals($resToken,$token))
            return view ('pages.photo',['photo'=>$photoId,'owner'=>$owner,'name'=>$name,'phone'=>$phone,'sh'=>$sharing,'token'=>$token]);
        else return view('pages.error',['photo'=>$photoId,'phone'=>$phone,'token'=>$token]);
    }

    private function changeSharing($photoId,$name,$phone,$rhname,$rhphone,$sharing){
        $photo = Photo::findOrFail($photoId);
        if (!$photo)
            return view('pages.error');
        $obj = json_decode($photo->data);

        $sh  = str_split($sharing);
        $shdata = ['facebook'=>$sh[0]=='1','twitter'=>$sh[1]=='1','instagram'=>$sh[2]=='1','web'=>$sh[3]=='1'];

        // Se busca el rightholder que ha aceptado y se cambia su sharing
        foreach ($obj->people as $person) {
            if ($person->name == $name && $person->phone == $phone) {
                foreach ($person->rightholders as $rh) {
                    if ($rh->name == $rhname && $rh->phone == $rhphone){
                        $rh->sharing = $shdata;
                        $photo->data = json_encode($obj);
                        $photo->save();
                    }
                }
            }
        }
    }

    public function response($photoId,$owner,$name,$phone,$rhname,$rhphone,$sharing,$token,$response)
    {
        $resToken = Token::generate($photoId,$owner,$name,$phone,$rhname,$rhphone);

        if (hash_equals($resToken,$token)){
            if($response=='si') {
                $linkback = route('photo.link', ['id' => $photoId,'owner'=>$owner, 'name'=>$name, 'phone'=>$phone,'rhname'=>$rhname, 'rhphone' => $rhphone, 'sharing' => $sharing, 'token' => $token]);
                // Guardamos la respuesta

                $this->changeSharing($photoId,$name,$phone,$rhname,$rhphone,$sharing);


                return view('pages.photosi',['link'=>$linkback,'owner'=>$owner,'sh'=>$sharing]);
            }
            else {
                // Guardamos la respuesta
                $this->changeSharing($photoId,$name,$phone,$rhname,$rhphone,"0000");
                return view('pages.photono');
            }
        }
        else return view('pages.error',['photo'=>$photoId,'phone'=>$phone,'token'=>$token]);
    }

}
