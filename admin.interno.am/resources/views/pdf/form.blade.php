<!DOCTYPE html>
<html>
<head>
    <meta http-equiv=Content-Type content="text/html; charset=UTF-8">
    <meta name=Generator content="Microsoft Word 15 (filtered)">
    <style>
        .MsoBodyTex.custom-text p {
            margin-left: 5.8pt
        }

        @font-face {
            font-family: "Tahoma";
            panose-1: 2 4 5 3 5 4 6 3 2 4;
        }

        @font-face {
            font-family: Tahoma;
            panose-1: 2 15 5 2 2 2 4 3 2 4;
        }

        @font-face {
            font-family: "Tahoma";
        }

        p.MsoNormal, li.MsoNormal, div.MsoNormal {
            margin: 0cm;
            text-autospace: none;
            font-size: 11.0pt;
            font-family: "Tahoma", sans-serif;
        }

        h1 {
            margin-top: 0cm;
            margin-right: 0cm;
            margin-bottom: 0cm;
            margin-left: 5.8pt;
            text-autospace: none;
            font-size: 11.0pt;
            font-family: "Tahoma", sans-serif;
        }

        p.MsoBodyText, li.MsoBodyText, div.MsoBodyText {
            margin: 0cm;
            text-autospace: none;
            font-size: 11.0pt;
            font-family: "Tahoma", sans-serif;
        }

        p.TableParagraph, li.TableParagraph, div.TableParagraph {
            mso-style-name: "Table Paragraph";
            margin-top: 3.6pt;
            margin-right: 0cm;
            margin-bottom: 0cm;
            margin-left: 8.0pt;
            text-autospace: none;
            font-size: 11.0pt;
            font-family: "Tahoma", sans-serif;
        }

        .MsoChpDefault {
            font-family: "Tahoma", sans-serif;
        }

        .MsoPapDefault {
            text-autospace: none;
        }

        /* Page Definitions */
        @page WordSection1 {
            size: 595.5pt 842.0pt;
            margin: 68.0pt 65.0pt 52.0pt 65.0pt;
        }

        div.WordSection1 {
            page: WordSection1;
        }

        @page WordSection2 {
            size: 595.5pt 842.0pt;
            margin: 68.0pt 65.0pt 52.0pt 65.0pt;
        }

        div.WordSection2 {
            page: WordSection2;
        }

        @page WordSection3 {
            size: 595.5pt 842.0pt;
            margin: 68.0pt 65.0pt 52.0pt 65.0pt;
        }

        div.WordSection3 {
            page: WordSection3;
        }

        .top-text, p {
            font-size: 14px !important;
        }
    </style>
</head>
<body lang=RU style='word-wrap:break-word'>

<div>
    <h1 style='margin-top:2.3pt;font-size:15pt;text-align: center;'>
        <span lang=EN-US>
            {{ $translation['pdf_personal_data_of_the_person_providing_the_images_videos'] ?? '{pdf_personal_data_of_the_person_providing_the_images_videos}' }}:
        </span>
    </h1>
    <br>
    <p class=MsoNormal
       style='margin-top:4.15pt;margin-right:0cm;margin-bottom:0cm;margin-left:0;margin-bottom:.0001pt;line-height:12.05pt'>
        <strong>
            {{ $siteName->value ?? ($siteName->base_value ?? '') }}
        </strong>
    </p>
    <div>
        {!! $address->value ?? ($address->base_value ?? '') !!}
    </div>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.5pt;font-family:"Tahoma",sans-serif'>&nbsp;</span></p>
    <p class=MsoBodyText style='margin-top:.15pt'><span lang=EN-US
                                                        style='font-size:12.5pt;font-family:"Tahoma",sans-serif'>&nbsp;</span>
    </p>
    <h1 style="font-size:9pt">
        <span lang=EN-US>{{ $translation['pdf_personal_data_of_the_person_providing_the_images_videos'] ?? '{pdf_personal_data_of_the_person_providing_the_images_videos}' }}</span>
    </h1>
    <p class=MsoBodyText style='margin-top:.4pt'><i><span lang=EN-US style='font-size:5.0pt'>&nbsp;</span></i></p>
    <table class=TableNormal cellspacing=0 cellpadding=0 style='border-collapse:separate;border:none;border-spacing:5px; width: 100%;'>
        @if(!empty($formContent))
            @foreach($formContent as $key => $fContent)
                @if($fContent)
                    @php
                        $label = str_replace('_', ' ', $key);
                        $label = ucwords($label);
                    @endphp
                    <tr>
                        <td width=596 valign=top style='width:446.95pt;border:1pt solid black; background:#DAE2F3;padding:5px 10px; '>
                            <p class=TableParagraph>
                                <span lang=EN-US>{{ $label }}: {{ $fContent }}</span>
                            </p>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
    </table>
    <div class="MsoBodyTex custom-text" style='font-size:10.5pt;line-height:150%; font-family:"Tahoma",sans-serif; margin-left: 0;'>
        {!! $topText !!}
    </div>
</div>
<pagebreak></pagebreak>

@if (!empty($files_group))
    <table>
        @foreach ($files_group as $group)
            <tr>
                @foreach ($group as $file)
                    <td style="text-align: center" align="center" valign="center">
                        <img src="{{ $file }}"
                             alt=""
                             style="width: 200px; height: auto;"
                             width="200">
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
@endif
@if (!empty($filesGroup))
    <table>
        @foreach ($filesGroup as $group)
            <tr>
                @foreach ($group as $image)
                    <td style="text-align: center" align="center" valign="center">
                        <img src="{{ $image }}"
                             alt=""
                             style="width: 200px; height: auto;"
                             width="200">
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
@endif

@if (!empty($videosArr))
    <div>
        <p class="MsoBodyText">
            <span lang="EN-US" style="font-size:10.5pt;font-family:'Tahoma',sans-serif;">&nbsp;</span>
        </p>
        <p class="MsoBodyText">
            <span lang="EN-US" style="font-size:10.5pt;font-family:'Tahoma',sans-serif;">&nbsp;</span>
        </p>
        <p class="MsoNormal" style="margin-top:4.15pt;margin-right:0;margin-bottom:0;margin-left:5.8pt;margin-bottom:.0001pt;line-height:12.05pt;">
            <b>
                <span lang="EN-US" style="font-size:10.5pt;font-family:'Tahoma',sans-serif;color:#202429;">
                    {{ $translation['pdf_videos'] ?? '{pdf_videos}' }}
                </span>
            </b>
        </p>
        <ol>
            @foreach ($videosArr as $video)
                <li style="font-size:10.5pt;line-height:12.05pt;font-family:'Tahoma',sans-serif;color:#202429;">
                    {{ $video }}
                </li>
            @endforeach
        </ol>
    </div>
@endif
<div>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText style='margin-top:.35pt'><span lang=EN-US style='font-size:11.5pt'>&nbsp;</span></p>
    <div class="MsoBodyTex custom-text"
         style='font-size:10.5pt;line-height:150%; font-family:"Tahoma",sans-serif; margin-left: 0;'>
        {!! $bottomText !!}
    </div>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText><span lang=EN-US style='font-size:10.0pt'>&nbsp;</span></p>
    <p class=MsoBodyText style='margin-top:.45pt'>
        <br clear=ALL>
    </p>
    <p class=MsoBodyText style='margin-top:.3pt'><span lang=EN-US style='font-size:
4.5pt'>&nbsp;</span></p>
    <table style="width: 100%;">
        <tr>
            <td style="text-align:center;width:35%;font-size:10.5pt;line-height:150%;font-family:Tahoma,sans-serif;">
                {{ date('d.m.Y') }}
                <hr>
                <br>
                {{ $translation['pdf_date'] ?? '{pdf_date}' }}
            </td>
            <td style="width:30%;"></td>
            <td style="text-align:center;width:35%;font-size:10.5pt;line-height:150%;font-family:Tahoma,sans-serif;">
                {{ reset($formContent) }}
                <hr>
                <br>
                {{ $translation['pdf_signature'] ?? '{pdf_signature}' }}
            </td>
        </tr>
    </table>
</div>
</body>
</html>
