<?php

$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        $receive_email = $row['receive_email'];
		$logo = $row['logo'];
		$color_email = $row['color_email'];
        $footer_copyright = $row['footer_copyright'];
    	$social_facebook = $row['social_facebook'];
    	$social_twitter = $row['social_twitter'];
    }

define('HEADER', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pt_br" xml:lang="pt_br">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <style type="text/css">
        .contextualExtensionHighlight{
            color: #8295b9 !important;
            text-decoration: none;
            border-style: none;
        }
         
      </style>
    <!--[if gte mso 9]>
    	
    <style type="text/css">
        .container-radius{
            padding-left: 48px;
            padding-right: 48px;
            padding-top 32px;
        }
        a{
            text-decoration: none;
            text-underline-style: none;
            text-underline-color: none;
        }
        .padding-title{
            padding-top: 10px;
            padding-bottom:10px;
            padding-left: 8px;
            padding-right: 8px;
            margin-top: 16px;
            margin-bottom: 0;
        }
        @media only screen and (max-width: 628px) {
            .small-float-center {
                margin: 0 auto !important;
                float: none !important;
                text-align: center !important
            }
            .container-radius{
                border-spacing: 0 !important;
                padding-left: 16px!important;
                padding-right: 16px!important;
                padding-top: 16px!important;
            }
        }
    </style>
    <![endif]-->

    <!--[if mso]>
    <style>
        .padding-title{
            padding-top: 10px;
            padding-bottom:10px;
            padding-left: 8px;
            padding-right: 8px;
            margin-bottom: 0;
            margin-top:16px;
        }
        .container-radius {
            padding-top: 32px;
        }
        @media only screen and (max-width: 628px) {
            .small-float-center {
                margin: 0 auto !important;
                float: none !important;
                text-align: center !important
            }
            .container-radius{
                border-spacing: 0 !important;
                padding-left: 16px !important;
                padding-right: 16px !important;
                padding-top: 16px !important;
            }
        }
    </style>
    <![endif]-->
</head>
<body style="-moz-box-sizing:border-box;-ms-text-size-adjust:100%;-webkit-box-sizing:border-box;-webkit-text-size-adjust:100%;Margin:0;box-sizing:border-box;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;min-width:100%;padding:0;text-align:left;width:100%!important">
<span class="preheader"
      style="color:#fff;display:none!important;font-size:1px;line-height:1px;max-height:0;max-width:0;mso-hide:all!important;opacity:0;overflow:hidden;visibility:hidden"></span>

<table class="body"
       style="Margin: 0;background-color: '.$color_email.';border-collapse:collapse;border-color:transparent;border-spacing:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;height:100%;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;width:100%;">
    <!--[if gte mso 9]>
    <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
        <v:fill type="tile" color="#fff"/>
    </v:background>
    <![endif]-->
    <tr style="padding:0;text-align:left;vertical-align:top">
        <td class="center" align="center" valign="top"
            style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
            <center data-parsed="" style="min-width:580px;width:100%">
                <table class="spacer float-center"
                       style="Margin:0 auto;border-collapse:collapse;border-color:transparent;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:100%">
                    <tbody>
                    <tr style="padding:0;text-align:left;vertical-align:top">
                        <td height="40px"
                            style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:40px;font-weight:400;hyphens:auto;line-height:40px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                            &#xA0;
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table align="center" class="container header float-center"
                       style="Margin:0 auto;background:0 0;border-collapse:collapse;border-color:transparent;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:580px;max-width:580px;">
                    <tbody>
                    <tr style="padding:0;text-align:left;vertical-align:top">
                        <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                            <table class="row collapse logo-wrapper"
                                   style="background:0 0;border-collapse:collapse;border-color:transparent;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                                <tbody>
                                <tr style="padding:0;text-align:left;vertical-align:top">
                                    <th class="small-12 large-6 columns first"
                                        style="Margin:0 auto;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0;padding-bottom:0;padding-left:0;padding-right:0;text-align:left;width:200px">
                                        <table style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                                            <tr style="padding:0;text-align:left;vertical-align:top">
                                                <th valign="middle" height="49" style="Margin:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left">
                                                    <img width="200" class="header-logo"
                                                         src="'.BASE_URL.'assets/uploads/'.$logo.'" alt="Logo"
                                                         style="-ms-interpolation-mode:bicubic;clear:both;display:block;max-width:220px;width:auto;height:auto;outline:0;text-decoration:none;max-height:49px">
                                                </th>
                                            </tr>
                                        </table>
                                    </th>
                                    <th class="small-12 large-6 columns last"
                                        style="Margin:0 auto;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0;padding-bottom:0;padding-left:0;padding-right:0;text-align:right;width:320px;vertical-align:middle;">
                                        <table style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:right;vertical-align:top;width:100%">
                                            <tr style="padding:0;text-align:right;vertical-align:top">
                                                <th style="Margin:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:right">
                                                    <span class="header-top-text"
                                                          style="border-left-width:7px;border-left-color:transparent;border-left-style:solid;color:#fefeff;display:table;font-size:12px;line-height:29px;text-align:right;margin-left:auto;"> Dúvidas?<a
                                                            class="faded-link" href="'.BASE_URL.'pagina/fale-conosco"
                                                            style="Margin:0;text-decoration:none;color:#8295b9!important;font-family:Roboto,sans-serif;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left;text-decoration:none;line-height:8px;"> <span style="text-decoration:none; color:#fefeff"><font color="#fefeff">Fale Conosco</font></span></a></span>
                                                </th>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="spacer float-center"
                       style="Margin:0 auto;border-collapse:collapse;border-color:transparent;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:100%">
                    <tbody>
                    <tr style="padding:0;text-align:left;vertical-align:top">
                        <td height="32px"
                            style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:32px;font-weight:400;hyphens:auto;line-height:32px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                            &#xA0;
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table cellspacing="0" cellpadding="0" border="0" align="center" class="container body-drip float-center"
                       style="Margin:0 auto;border-bottom-left-radius:3px;border-bottom-right-radius:3px;border-collapse:collapse;border-color:transparent;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:580px;max-width:580px">
                    <tbody>
                    <tr style="padding:0;text-align:left;vertical-align:top">
                        <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word;max-width:580px!important">
                            <hr align="center" class="float-center"
                                 style="background:#ffffff;border:none;color:#ffffff;height:1px;margin-bottom:10;margin-top:10">
                            <table class="container-radius"
                                   style="border-left-color:#e6e6e6!important;border-bottom-color:#e6e6e6!important;border-right-color:#e6e6e6!important; border-width:0;border-style:solid;border-bottom-left-radius:3px;border-bottom-right-radius:3px;border-color:#e6e6e6;border-top:none;display:table-cell;padding-bottom:32px;border-spacing:48px 0;border-collapse:separate;width:100%;background:#fff;max-width:580px;">
                                <tbody>
                                <tr>
                                    <td>

                                        <table class="row"
                                               style="border-collapse:collapse;border-color:transparent;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                                            <tbody>
                                            <tr style="padding:0;text-align:left;vertical-align:top"></tr>
                                            </tbody>
                                        </table>
                                        <table class="spacer"
                                               style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                                            <tbody>
                                            <tr style="padding:0;text-align:left;vertical-align:top">
                                                <td height="32px"
                                                    style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:32px;font-weight:400;hyphens:auto;line-height:32px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>');
                                        define('BODY', '<p>#body#</p>');
                                        define('FOOTER', ' </td>
                            </tr>
                            </tbody>

                        </table>
                        <hr align="center" class="float-center"
    style="background:#ffffff;border:none;color:#ffffff;height:1px;margin-bottom:10;margin-top:10">
<table class="spacer"
       style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">

    <tbody>
    <tr style="padding:0;text-align:left;vertical-align:top">
        <td height="40px"
            style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:40px;font-weight:400;hyphens:auto;line-height:40px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
            &#xA0;

        </td>

    </tr>
    </tbody>

</table>

<table align="left" class="container aside-content"
       style="Margin:0 auto;background:0 0;border-collapse:collapse;border-color:transparent;border-spacing:0;margin:0 auto;padding:0;text-align:inherit;vertical-align:top;width:580px">
    <tbody>
    <tr style="padding:0;text-align:left;vertical-align:top">
        <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
            <table class="row row-wide"
                   style="border-collapse:collapse;border-color:transparent;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                <tbody>
                <tr style="padding:0;text-align:left;vertical-align:top">
                    <th class="small-12 large-8 columns first"
                        style="Margin:0 auto;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;padding-bottom:16px;padding-left:0;padding-right:0;text-align:left;width:437px;vertical-align:middle;">
                        <table style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                            <tr style="padding:0;text-align:left;vertical-align:top">
                                <th style="Margin:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0!important;text-align:left">
                                    <p style="Margin:0;Margin-bottom:10px;margin-top:10px!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;margin-bottom:10px;padding:0;text-align:left">
                                        <a class="footer-link-color" href="'.BASE_URL.'"
                                           style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left;text-decoration:none"><b style="text-underline-style: none;border-bottom:none;border-top:none;"><font color="#fafafa">'.BASE_URL.'</font></b></a>
                                    </p></th>
                            </tr>
                        </table>
                    </th>
                    <th class="small-12 large-4 columns last"
                        style="Margin:0 auto;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;padding-bottom:16px;padding-left:0;padding-right:0!important;text-align:left;width:120px">
                        <table style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:right;vertical-align:top;width:100%">
                            <tr style="padding:0;text-align:right;vertical-align:top">
                                <th style="Margin:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0!important;text-align:left">
                                    <table class="menu"
                                           style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:auto;margin-left:auto;border-spacing:0;">
                                        <tr style="padding:0;text-align:left;vertical-align:top">
                                            <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                                                <table style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:right;vertical-align:top;width:100%">
                                                    <tr style="padding:0;text-align:left;vertical-align:top">
                                                        
														<th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0!important;" width="16px"></th>
                                                        <th class="menu-item float-center"
                                                            style="Margin:0 auto;color:#0a0a0a;float:none;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:4px 0!important;text-align:right">
                                                            <a href="' . $social_facebook . '"
                                                               style="Margin:0;color:#0057FF;font-family:Roboto,sans-serif;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left;text-decoration:none"><span
                                                                    class="rounded-button"
                                                                    style="align-items:center;display:flex;float:right;height:42px;justify-content:center;width:42px;"><img
                                                                    src="'.BASE_URL.'assets/PHPmailer/image/fb.png"
                                                                    alt=""
                                                                    style="-ms-interpolation-mode:bicubic;border:none;clear:both;display:block;max-width:100%;outline:0;text-decoration:none;width:auto"> </span></a>
                                                        </th>
														
 													    <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                        <th class="menu-item float-center"
                                                            style="Margin:0 auto;color:#0a0a0a;float:none;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:4px 0!important;text-align:center">
                                                            <a href="' . $social_twitter . '"
                                                               style="Margin:0;color:#0057FF;font-family:Roboto,sans-serif;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left;text-decoration:none"><span
                                                                    class="rounded-button"
                                                                    style="align-items:center;display:flex;float:right;height:42px;justify-content:center;width:42px;"><img
                                                                    src="'.BASE_URL.'assets/PHPmailer/image/t.png"
                                                                    alt=""
                                                                    style="-ms-interpolation-mode:bicubic;border:none;clear:both;display:block;max-width:100%;outline:0;text-decoration:none;width:auto"> </span></a>
                                                        </th>
                                                        
                                                        <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                        <th class="menu-item float-center"
                                                            style="Margin:0 auto;color:#0a0a0a;float:none;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:4px 0!important;text-align:center">
                                                          
                                                        </th>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                    </th>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
 
</td>
</tr>
</tbody>
</table>
<table class="spacer float-center"
       style="Margin:0 auto;border-collapse:collapse;border-color:transparent;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:100%">
    <tbody>
    <tr style="padding:0;text-align:left;vertical-align:top">
        <td height="40px"
            style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:40px;font-weight:400;hyphens:auto;line-height:40px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
            &#xA0;
        </td>
    </tr>
    </tbody>
</table>

<table align="center" class="container aside-content float-center"
       style="Margin:0 auto;background:0 0;border-collapse:collapse;border-color:transparent;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:left;vertical-align:top;width:580px;margin-bottom: 24px;">
    <tbody>
	<hr align="center" class="float-center"
    style="background:#ffffff;border:none;color:#ffffff;height:1px;margin-bottom:10;margin-top:10">
    <tr style="padding:0;text-align:left;vertical-align:top">
        <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:1.3;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
            <table class="row collapsed footer"
                   style="border-collapse:collapse;border-color:transparent;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                <tbody>
                <tr style="padding:0;text-align:left;vertical-align:top">
                    <table class="row row-wide"
                           style="border-collapse:collapse;border-color:transparent;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                        <tbody>
                        <tr style="padding:0;text-align:left;vertical-align:top">
                            <th class="small-12 large-12 columns first last"
                                style="Margin:0 auto;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;padding-bottom:16px;text-align:left;width:532px">
                                <table style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                                    <tr style="padding:0;text-align:left;vertical-align:top">
                                        <th style="Margin:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0!important;text-align:left">
                                            <table class="spacer"
                                                   style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                                                <tbody>
                                                <tr style="padding:0;text-align:left;vertical-align:top">
                                                    <td height="16px"
                                                        style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:16px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                                                        &#xA0;
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>

                                            <table style="Margin:0;Margin-bottom:10px;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:12px;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left;border-spacing:0;">
                                                <tbody>
                                                <tr>
                                                    <th class="small-12 large-2 columns first" tabindex="0" role="button" style="text-decoration:none;padding-left:0!important;text-align:left !important;" align="left">
                                                        <a class="footer-link" role="link" target="_blank" rel="noopener" href="'.BASE_URL.'acessar-conta" style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;cursor:pointer;font-size:12px;font-weight:400;line-height:29px;display:inline-block;margin:0;padding:0;text-align:left;text-decoration:none;line-height:18px"><font color="#ffffff">Acesso a Conta</font></a>
                                                    </th>
                                                    <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                    <th class="small-12 large-2 columns" tabindex="0" role="button" style="text-decoration:none;padding-left:0!important;text-align:left !important;" align="left">
                                                        <a class="footer-link" role="link" target="_blank" rel="noopener" href="'.BASE_URL.'pagina/fale-conosco" style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;cursor:pointer;font-size:12px;font-weight:400;line-height:29px;display:inline-block;margin:0;padding:0;text-align:left;text-decoration:none;line-height:18px"><font color="#ffffff">Suporte</font></a>
                                                    </th>
                                                    <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                    <th class="small-12 large-2 columns" tabindex="0" role="button" style="text-decoration:none;padding-left:0!important;text-align:left !important;" align="left">
                                                        <a class="footer-link" role="link" target="_blank" rel="noopener" href="'.BASE_URL.'pagina/termos-de-uso" style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;cursor:pointer;font-size:12px;font-weight:400;line-height:29px;display:inline-block;margin:0;padding:0;text-align:left;text-decoration:none;line-height:18px"><font color="#ffffff">Termos de uso</font></a>
                                                    </th>
                                                    <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                    <th class="small-12 large-2 columns" tabindex="0" role="button" style="text-decoration:none;padding-left:0!important;text-align:left !important;" align="left">
                                                        <a class="footer-link" role="link" target="_blank" rel="noopener" href="'.BASE_URL.'pagina/politica-de-privacidade" style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;cursor:pointer;font-size:12px;font-weight:400;line-height:29px;display:inline-block;margin:0;padding:0;text-align:left;text-decoration:none;line-height:18px"><font color="#ffffff">Políticas de privacidade</font></a>
                                                    </th>
                                                    <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                    <th class="small-12 large-2 columns last" tabindex="0" role="button" style="text-decoration:none;padding-left:0!important;text-align:left !important;" align="left">
                                                        <a class="footer-link" role="link" target="_blank" rel="noopener" href="'.BASE_URL.'pagina/ajuda-e-suporte" style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;cursor:pointer;font-size:12px;font-weight:400;line-height:29px;display:inline-block;margin:0;padding:0;text-align:left;text-decoration:none;line-height:18px"><font color="#ffffff">Ajuda e Publicidade</font></a>
                                                    </th>
													<th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                    <th class="small-12 large-2 columns last" tabindex="0" role="button" style="text-decoration:none;padding-left:0!important;text-align:left !important;" align="left">
                                                        <a class="footer-link" role="link" target="_blank" rel="noopener" href="'.BASE_URL.'pagina/planos" style="Margin:0;color:#8295b9;font-family:Roboto,sans-serif;cursor:pointer;font-size:12px;font-weight:400;line-height:29px;display:inline-block;margin:0;padding:0;text-align:left;text-decoration:none;line-height:18px"><font color="#ffffff">Planos</font></a>
                                                    </th>
                                                    <th style="Margin:0 auto;color:#0a0a0a;width:16px;display:inline-block;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0!important;" width="16px"></th>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <table class="spacer"
                                                   style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%;background:transparent">
                                                <tbody>
                                                <tr style="padding:0;text-align:left;vertical-align:top">
                                                    <td height="16px"
                                                        style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:16px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
                                                        &#xA0;
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <span class="footer-description" style="color:#8295b9;font-size:11px!important;line-height:18px;"><span style="text-decoration:none;color:#8295b9!important;font-size:11px;"><font color="#ffffff">' . $footer_copyright . '</font></span></span>
                                        </th>
                                        <th class="expander"
                                            style="Margin:0;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0!important;text-align:left;visibility:hidden;width:0"></th>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</center>
</td>
</tr>
</table><!-- prevent Gmail on iOS font size manipulation -->
<div style="display:none;white-space:nowrap;font:15px courier;line-height:0">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
</div>
<table class="spacer"
       style="border-collapse:collapse;border-color:transparent;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%;background:transparent">
    <tbody>
    <tr style="padding:0;text-align:left;vertical-align:top">
        <td height="16px"
            style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Roboto,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:16px;margin:0;mso-line-height-rule:exactly;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
            &#xA0;
        </td>
    </tr>
    </tbody>
</table>
</body></html>');

function SendMail($email, $nome, $assunto, $texto) {
	
	  $headers = 'From: ' . $email . "\r\n" .
                'Reply-to: ' . $email . "\r\n" .
                'X-Mailer: PHP/' . phpversion() . "\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/html; charset=UTF-8\r\n";

        // Sending email to admin				   
      return  mail($email, $assunto, HEADER . str_replace("#body#", $texto, BODY) . FOOTER, $headers);
     
     
}


