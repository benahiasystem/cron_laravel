<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\BlackList;

use App\Mail\MailRegisterFirst;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailEnviarCorreoFirstControl;

use App\Mail\MailRegisterSecond;
use App\Mail\MailRegisterSecond2;
use App\Mail\MailEnviarCorreoSecondControl;

use App\Mail\MailRegisterThird;
use App\Mail\MailEnviarCorreoThirdControl;

use App\Mail\MailRegisterFourth;
use App\Mail\MailEnviarCorreoFourthControl;

use App\Mail\MailRegisterFifth;
use App\Mail\MailRegisterFifth2;
use App\Mail\MailEnviarCorreoFifthControl;

use App\Mail\MailRegisterSixth;
use App\Mail\MailEnviarCorreoSixthControl;

class SendMails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->sendMailRegisterFirst();
        $this->sendMailRegisterSecond();
        $this->sendMailRegisterThird();
        $this->sendMailRegisterFourth();
        $this->sendMailRegisterFifth();
        $this->sendMailRegisterSixth();
         
    }

    public function sendMailRegisterFirst()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
        $end_date = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 1, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    
                    $this->EnviarCorreoFirst((string)$email);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoFirstControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }


    }


    public function sendMailRegisterSecond()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')));
        $end_date = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 2, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoSecond((string)$email);
                    $this->EnviarCorreoSecond2((string)$email, (string)$key['nombre'], (string)$key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoSecondControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }    

    public function sendMailRegisterThird()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 3, date('Y')));
        $end_date = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 3, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoThird((string)$email, (string)$key['nombre'], (string)$key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoThirdControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }   


    public function sendMailRegisterFourth()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 4, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 4, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoFourth((string)$email, (string)$key['nombre'], (string)$key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoFourthControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }    

    public function sendMailRegisterFifth()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 5, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 5, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoFifth((string)$email);
                    $this->EnviarCorreoFifth2((string)$email);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoFifthControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }

    public function sendMailRegisterSixth()
    {
        $count  = 0;
        $emails = "";

        $blackListedMails = Blacklist::all()->toArray();
        
        $start_date     = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 6, date('Y')));
        $end_date       = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 6, date('Y')));

        $registros =  json_decode(Registros($start_date, $end_date), TRUE);
        
        try {
            foreach ($registros as $key) {
                $email = $key['correo'];
            
                $puede  = checkCanSend($blackListedMails, $email);

                if ($puede) {
                    $this->EnviarCorreoSixth((string)$email, (string)$key['nombre'], (string)$key['apellidos']);
                }

                $count++;
                $emails .= $key['nombre'] . ' ' . $key['apellidos'] . ' - ' . $key['correo'] . '<br>';
            }
        } catch (Exception $e) {
            $emails .= 'Excepción capturada: ' . $e->getMessage() . '<br>';
        }


        try {
            $this->EnviarCorreoSixthControl($count, $emails);
        }catch(Exception $e) {
            return $e;
        }
    }

    
    /*----------------------------------------------------------*/
    /* FUNCTIONES ADICIONALES                                   */
    /*----------------------------------------------------------*/

    public function EnviarCorreoFirst($email) {
        Mail::to($email)
        ->send(new MailRegisterFirst($email));
    }

    public function EnviarCorreoSecond($email) {
        Mail::to($email)
        ->send(new MailRegisterSecond($email));
    }

    public function EnviarCorreoSecond2($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterSecond2($email, $nombre, $apellidos));
    }

    public function EnviarCorreoThird($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterThird($email, $nombre, $apellidos));
    }

    public function EnviarCorreoFourth($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterFourth($email, $nombre, $apellidos));
    }
    public function EnviarCorreoFifth($email) {
        Mail::to($email)
        ->send(new MailRegisterFifth($email));
    }

    public function EnviarCorreoFifth2($email) {
        Mail::to($email)
        ->send(new MailRegisterFifth2($email));
    }

    public function EnviarCorreoSixth($email, $nombre, $apellidos) {
        Mail::to($email)
        ->send(new MailRegisterSixth($email, $nombre, $apellidos));
    }

    public function EnviarCorreoFirstControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoFirstControl($count, $emails));
    }

    public function EnviarCorreoSecondControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoSecondControl($count, $emails));
    }

    public function EnviarCorreoThirdControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoThirdControl($count, $emails));
    }

    public function EnviarCorreoFourthControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoFourthControl($count, $emails));
    }

    public function EnviarCorreoFifthControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoFifthControl($count, $emails));
    }

    public function EnviarCorreoSixthControl($count, $emails) {
        Mail::to('desarrollo@vendty.com')
        ->bcc(array('soporte@vendty.com', 'asesor@vendty.com', 'arnulfo@vendty.com', 'info@vendty.com', 'desarrollo@vendty.com', 'nelson@vendty.com'))
        ->send(new MailEnviarCorreoSixthControl($count, $emails));
    }

}
