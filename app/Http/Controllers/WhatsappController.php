<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Exception;

class WhatsappController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function enviar($mensagem, $destinos, $user = null) {

        if($user){
            $this->user = $user;
        }

        try {

            $curl = curl_init();

            foreach ($destinos as $destino) {

                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'https://api.zenvia.com/v2/channels/whatsapp/messages',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS =>'{
                                            "from":"thundering-stitch",
                                            "to": "'.$destino.'",
                                            "contents":[{
                                            "type": "text",
                                            "text": "'.$mensagem.'"
                                        }]}',
                  CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'X-API-TOKEN:DkEZ7lRsOdCBDYo9JvFxDw-JAh9XszQb6Hem'
                  ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                echo $mensagem;
                echo $destino;
            }

        } catch (Exception $e) {

            throw new Exception($e->getMessage());

        }
    }

}
