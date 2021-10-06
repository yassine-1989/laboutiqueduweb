<?php


namespace App\Classe;


use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    // Définition de la clé api et de la clé secréte récupéré de mon compte JetMail
    private $api_key='a5c96307a78a9245fc3fda32218f92bf';
    private $api_key_private='46350eb47b3f0dc5d58bc6105e3e82f5';

    public function send($to_email, $to_name, $subject, $content){
        // code récupéré de la doc JetMail
        $mj = new Client($this->api_key, $this->api_key_private,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "yasinos1989@gmail.com",
                        'Name' => "La Boutique Du Web"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 2411826,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables'=>[
                        'content'=>$content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() ;
    }

}