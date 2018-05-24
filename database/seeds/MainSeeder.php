<?php

use App\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;


class MainSeeder extends Seeder {

    private $faker = null;
    /**
     * Run the database seeds.
     *
     * @return  void
     */
    public function run()
    {
        //Empty the countries table
        DB::table('users')->delete();

        $this->faker = Faker::create();

        // ** LOCATIONS **
        $idlocation = DB::table('locations')->insertGetId( array(
            'name' => 'colegio1'

        ) );

        // ** USERS **
        $id = DB::table('users')->insertGetId( array(
            'name' => 'jose',
            'email' => 'jmgarciacarrasco@gmail.com',
            'phone' => '637455827',
            'location_id' => $idlocation,
            'password' => Hash::make('tec_002!')
        ) );

        $id1 = DB::table('users')->insertGetId( array(
            'name' => 'test1',
            'email' => 'test1@gmail.com',
            'phone' => '637455826',
            'location_id' => $idlocation,
            'password' => Hash::make('tec_002!'),
        ) );

        $id2 = DB::table('users')->insertGetId( array(
            'name' => 'test2',
            'email' => 'test2@gmail.com',
            'phone' => '637455828',
            'location_id' => $idlocation,
            'password' => Hash::make('tec_002!'),
        ) );

        // ** PROFILES **
        DB::table('profiles')->delete();
        $this->createProfile($id,$idlocation,'super');
        $this->createProfile($id1,$idlocation,'admin');
        $this->createProfile($id2,$idlocation,'owner');
/*
        // ** GROUPS **
        DB::table('groups')->delete();
        $group1 = $this->createGroup($idlocation,$id1,'2B');
        $g1_twitter = $this->addPublicationSite($group1,'twitter');
        $g1_facebook = $this->addPublicationSite($group1,'facebook');
        $g1_instagram = $this->addPublicationSite($group1,'instagram');
        $group2 = $this->createGroup($idlocation,$id1,'3A');
        $g2_twitter = $this->addPublicationSite($group2,'twitter');
        $g2_facebook = $this->addPublicationSite($group2,'facebook');
        $g2_instagram = $this->addPublicationSite($group2,'instagram');


        // ** PERSONS **
        $this->addPersons($idlocation,$group1);
        $this->addPersons($idlocation,$group2);


        // ** PHOTOS **
        // GROUP 1
        $persons1 = DB::table('persons')->where('group_id',$group1);

        for($j=0; $j< 10 ; $j++) {
            $idPhoto = $this->addPhoto($idlocation);
            // ** PUBLICATION SITES **
            $this->addPhotoSite($idPhoto,$g1_twitter);
            $this->addPhotoSite($idPhoto,$g1_facebook);

            // ** CONTRATOS, en la foto salen 3**
            $personId1 = $this->faker->randomElement($persons1->pluck('id')->all());
            $personId2 = $this->faker->randomElement($persons1->pluck('id')->all());
            $personId3 = $this->faker->randomElement($persons1->pluck('id')->all());
            $c1 = $this->addContract($idlocation,$idPhoto,$personId1);
            $c2 = $this->addContract($idlocation,$idPhoto,$personId2);
            $c3 = $this->addContract($idlocation,$idPhoto,$personId3);
        }

        // GROUP 1
        $persons2 = DB::table('persons')->where('group_id',$group2);
        for($j=0; $j< 10 ; $j++) {
            $idPhoto = $this->addPhoto($idlocation);
            // ** PUBLICATION SITES **
            $this->addPhotoSite($idPhoto,$g2_twitter);
            $this->addPhotoSite($idPhoto,$g2_facebook);

            // ** CONTRATOS, en la foto salen 3**
            $personId1 = $this->faker->randomElement($persons2->pluck('id')->all());
            $personId2 = $this->faker->randomElement($persons2->pluck('id')->all());
            $this->addContract($idlocation,$idPhoto,$personId1);
            $this->addContract($idlocation,$idPhoto,$personId2);

        }
*/

    }

    function createProfile($userId,$locationid,$type){
        DB::table('profiles')->insertGetId( array (
            'user_id'   => $userId,
            'phone'     => $this->faker->phoneNumber,
            'avatar'    => str_replace('http','https',$this->faker->imageUrl(64, 48)),
            'location_id' => $locationid,
            'type'          => $type
        ));
    }

    function createGroup($idlocation,$userId,$name){
        $id = DB::table('groups')->insertGetId( array (
            'user_id'       => $userId,
            'location_id'   => $idlocation,
            'name'          => $name ));
        return $id;
    }

    function addPersons($idlocation,$groupId){
        for($i=0; $i< 20 ; $i++) {
            $idPerson = DB::table('persons')->insertGetId( array (
                'location_id'      => $idlocation,
                'group_id'      => $groupId,
                'photo'         => $this->faker->imageUrl(200,200,'people'),
                'name'          => $this->faker->text(10)));

            // ** RIGHTHOLDERS **
            $rightholder1 = $this->addRightHolders($idlocation,$idPerson);
            $rightholder2 = $this->addRightHolders($idlocation,$idPerson);


        }
    }

    function addRightHolders($idlocation,$idPerson){
        $id = DB::table('rightholders')->insertGetId( array (
            'location_id'      => $idlocation,
            'person_id'      => $idPerson,
            'name'           => $this->faker->text(10),
            'title'          => $this->faker->text(10),
            'email'          => $this->faker->email,
            'phone'          => $this->faker->phoneNumber ));
        return $id;
    }

    function addPhoto($idlocation){
        $idPhoto = DB::table('photos')->insertGetId( array (
            'location_id'      => $idlocation,
            'name'          =>$this->faker->text(10),
            'photo'          => $this->faker->imageUrl(300, 200,'people')
            ));
        return $idPhoto;
    }


    function addContract($idlocation,$photoId,$personId){
        $id = DB::table('contracts')->insertGetId( array (
        'location_id'      => $idlocation,
        'photo_id'       => $photoId,
        'person_id'      => $personId,

        ));

        if (isset($personId)) {
            //$rightholders = $person->rightholders();
            $rightholders = \App\Rightholder::where('person_id',$personId)->get();
            foreach ($rightholders as $holder) {
                $idHolder = DB::table('acks')->insertGetId( array (
                    'holder_id'      => $holder->id,
                    'contract_id'    => $id,
                    'status'         => $this->faker->randomElement([0,1,2]),

                ));
            }
        }
        return $id;
    }

    function addPublicationSite($idGroup,$url){

        $id = DB::table('publicationsites')->insertGetId( array (
            'group_id'      => $idGroup,
            'name'          => $this->faker->text(10),
            'url'           => $url
        ));
        return $id;
    }

    function addPhotoSite($idPhoto,$idSite){

        $id = DB::table('photosites')->insertGetId( array (
            'photo_id'              => $idPhoto,
            'publicationsite_id'    => $idSite
        ));
        return $id;
    }
}
