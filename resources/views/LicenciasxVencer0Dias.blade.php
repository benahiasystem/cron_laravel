<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>VENDTY - MODELO DE CORREO ELECTRONICO</title>
    <style type="text/css">
        body { margin: 0; padding: 0; min-width: 100%!important; }
        img { height: auto; }
        .content { width: 100%; max-width: 800px; }
        .header { padding: 20px 0px 0px 0px; background: #F3F3F3; }
        .innerpadding { padding: 30px 30px 30px 30px; }
        .innerpadding2 { padding: 10px; }
        .borderbottom { border-bottom: 1px solid #f2eeed; }
        .content-email { border: solid 1px lightgray; border-radius: 10px; background: #fff; padding: 20px 10px 20px 10px; }
        .subhead { font-size: 15px; color: #ffffff; font-family: sans-serif; letter-spacing: 10px; }
        .h1, .h2, .bodycopy { color: #153643; font-family: sans-serif; }
        .h1 { font-size: 33px; line-height: 38px; font-weight: bold; }
        .h2 { padding: 0 0 15px 0; font-size: 24px; line-height: 28px; font-weight: bold; }
        .bodycopy { font-size: 16px; line-height: 22px; }
        .button { text-align: center; font-size: 17px; font-family: sans-serif; font-weight: bold; padding: 0 30px 0 30px; }
        .button a { color: #ffffff; text-decoration: none; }
        .footer { background: #F3F3F3; }
        .footercopy { font-family: sans-serif; font-size: 14px; color: #979798; }
        .footercopy a { color: #979798; text-decoration: underline; }
        .footercopy .title a { color: #45af6d; font-weight: bold; text-decoration: none; }
        .footercopy .tel { color: #696969; font-weight: bold; }
        @media only screen and (max-width: 550px), screen and (max-device-width: 550px) {
            body[yahoo] .hide { display: none!important; }
            body[yahoo] .buttonwrapper { background-color: transparent!important; }
            body[yahoo] .button { padding: 0px!important; }
            body[yahoo] .button a { background-color: #e05443; padding: 15px 15px 13px!important; }
            body[yahoo] .unsubscribe { display: block; margin-top: 20px; padding: 10px 50px; background: #2f3942; border-radius: 5px; text-decoration: none!important; font-weight: bold; }
        }
        .logo { padding: 20px 0 20px 20px; }
        h1 { padding-left: 5px; color: #424242; font-family: Arial, Helvetica, sans-serif; font-size: 19px; line-height: 20px; padding-bottom: 5px; margin: 0; }
        h3 { color: #424242; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 20px; padding-bottom: 5px; margin: 0; margin-bottom: 10px; }
        .fecha, h2 { color: #9e9e9e; padding-left: 5px; font-family: sans-serif; font-size: 14px; font-weight: normal; line-height: 16px; margin: 0 0 10px 0; border-bottom: 1px solid #e5e5e5; }
        .templateColumns { border: solid 1px lightgray; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; }
        .column-table { background-color: #f5f5f5; padding: 10px 0px; }
        .flex { padding: 20px; box-sizing: border-box; border: solid 1px lightgray; -webkit-border-radius: 0px 0px 0px 5px; -moz-border-radius: 0px 0px 0px 5px; border-radius: 0px 0px 5px 5px; border-top: none; }
        .title-span { width: 100%; border-bottom: solid 1px #eceff1; color: #333; font-family: Arial, Helvetica, sans-serif; font-size: 15px; text-transform: uppercase; padding: 5px 10px 8px; }
        .description-span { color: #37474f; padding-left: 10px; font-family: sans-serif; font-weight: bold; font-size: 26px; line-height: 22px; margin: 0; margin-top: 12px; }
        .intro .cuenta { float: right; }
        .boton { text-align: right; }
        @media only screen and (max-width: 480px) {
            .logo { padding: 20px 0 20px 0px; display: inline-block !important; }
            .boton { margin-bottom: 17px; text-align: center; }
            /*.intro tbody{text-align:center;}*/
            .intro tbody tr td { display: block; }
            .templateColumnContainer { display: block !important; width: 100% !important; }
            .templateColumnContainer tbody { width: 100%; display: block; }
            .templateColumnContainer tbody tr { width: 100%; display: inline-table; padding-left: 5%; }
            .templateColumnContainer tbody tr td { vertical-align: top; }
        }
    </style>
</head>
<body bgcolor="F3F3F3">
    <table width="100%" bgcolor="F3F3F3" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table bgcolor="#ffffff" class="content" align="center" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header">
                            <table width="200" align="left" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td height="70" style="padding: 0 20px 20px 0;">
                                        <img class="fix" src="https://vendty-img.s3.amazonaws.com/logo-vendty.png" width="100%" border="0" alt="" style="margin-left:2rem;"/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="innerpadding2" style="background:#F3F3F3;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="innerpadding2 content-email">
                                <tr>
                                    <td class="h2">
                                        TU LICENCIA SERÁ SUSPENDIDA
                                    </td>
                                </tr>
                                <tr style="display: flex; justify-content: center;">
                                    <td>
                                        Estimado(a) <strong>{{ $nombres }}</strong><br>
                                        Tu servicio Vendty está pronto a vencerse.<br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bodycopy">
                                        <p>
                                            <table border="1" cellpadding="0" cellspacing="0" width="100%" class="licenciatable" style=" margin:auto;border-radius: 3px 3px 0 0;">
                                                <thead>
                                                    <th align="center">Licencia</th>
                                                    <th align="center">Fecha Inicio</th>
                                                    <th align="center">Fecha Vencimiento</th>
                                                    <th align="center">Almacén</th>
                                                    <th align="center">Valor</th>
                                                    <th align="center">Acción</th>
                                                </thead>
                                                <tbody>
                                                
                                                    @foreach($licencias as $key)
                                                        @if($email == $key['email'])
                                                            <tr align="center" valign="middle" style="color:#505050">
                                                                <td>{{ $key['nombre_plan'] }}</td>
                                                                <td style="color:#505050">{{ $key['fecha_inicio_licencia'] }}</td>
                                                                <td style="color:#505050">{{ $key['fecha_vencimiento'] }}</td>
                                                                <td style="color:#505050">{{ $almacen }}</td>
                                                                <td style="color:#505050">{{ $key['valor_plan'] }}</td>
                                                                <td style="color:#505050;padding: 5px 5px;"><a class="btn btn-success" target=_blank href="http://pos.vendty.com/index.php/frontend/configuracion">Pagar</a></td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <br>
                                            
                                            Gracias por elegirnos, equipo Vendty.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="alert alert-warning" align="justify" valign="top">
                                        <b style="color:red">NOTA <br></b>
                                        <i style="font-size:13px ">Nos encantaría que siguiera usando nuestros servicios, pero como en Vendty no hay cláusula de permanencia es su decisión continuar.
                                        Si no desea renovar le pedimos que descargue su información en archivos de Excel en la opción de Informes del sistema. Recuerde que su información será borrada de nuestros servidores pasados 30 días calendario y no podrá acceder a ella si está suspendido.</i>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer" bgcolor="#fff">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0 0 0;">
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="25%" style="text-align: center;padding: 0 10px 0 10px;">
                                                    <a target="_blank" href="https://twitter.com/vendtyapps">
                                                        <img src="https://vendty-img.s3.amazonaws.com/tw.png" width="30" height="30" alt="Twitter" border="0" />
                                                    </a>
                                                </td>
                                                <td width="25%" style="text-align: center;padding: 0 10px 0 10px;">
                                                    <a target="_blank" href="https://www.facebook.com/vendtycom">
                                                        <img src="https://vendty-img.s3.amazonaws.com/fb.png" width="30" height="30" alt="Facebook" border="0" />
                                                    </a>
                                                </td>
                                                <td width="25%" style="text-align: center;padding: 0 10px 0 10px;">
                                                    <a target="_blank" href="https://www.youtube.com/user/VendtyApps">
                                                        <img src="https://vendty-img.s3.amazonaws.com/yt.png" width="30" height="30" alt="Youtube" border="0" />
                                                    </a>
                                                </td>
                                                <td width="25%" style="text-align: center;padding: 0 10px 0 10px;">
                                                    <a target="_blank" href="https://www.linkedin.com/company/vendty-apps/">
                                                        <img src="https://vendty-img.s3.amazonaws.com/in.png" width="30" height="30" alt="Linkedin" border="0" />
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" class="footercopy">
                                        <br>
                                        <span class="title"><a target="_blank" href="https://ayuda.vendty.com/hc/es-419">Soporte<a> - <a target="_blank" href="https://ayuda.vendty.com/hc/es-419">Ayuda</a></span><br/><br/>

                                        Lunes - Viernes 8AM - 6PM<br/>
                                        Sábado: 8AM - 1PM<br/><br/>
                                        <br/><br/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
