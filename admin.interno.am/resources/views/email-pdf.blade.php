<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title></title>
    <link href="{{ public_path('/css/email-pdf.css') }}" rel="stylesheet" type="text/css"/>
</head>

<body>
<div id="IMO_24620" align=center width=1700 style="position:absolute; width: 100%">
    <table border=0 cellpadding=0 cellspacing=0 width=1700 style='border-collapse:collapse;'>
        <tr style='height:15.0pt'>
            <td height=20 class=xl6324620 width=38 style='height:15.0pt;width:29pt'></td>
            <td class=xl6724620 width=12 style='width:9pt'></td>
            <td class=xl6824620 colspan=7 width=168 style='width:126pt'>
                @if(!empty($parcelNumber))
                Bitte TNT-Frachtbriefnummer eintragen:
                @endif
            </td>
            <td class=xl6924620 width=24 style='width:18pt'></td>
            <td class=xl6724620 width=26 style='width:20pt'></td>
            <td class=xl6724620 width=25 style='width:19pt'></td>
            <td class=xl6724620 width=30 style='width:22pt'></td>
            <td class=xl6724620 width=32 style='width:24pt'></td>
            <td class=xl6724620 width=33 style='width:25pt'></td>
            <td class=xl6724620 width=53 style='width:40pt'></td>
            <td class=xl6724620 width=50 style='width:38pt'></td>
            <td class=xl6724620 width=32 style='width:24pt'></td>
            <td class=xl6724620 width=66 style='width:49pt'></td>
            <td class=xl6424620 width=38 style='width:29pt'></td>

        </tr>
        <tr style='height:15.6pt'>
            <td height=21 class=xl6324620 style='height:15.6pt'></td>
            <td class=xl6724620></td>
            <td class=xl6624620 colspan="9">
                <table
                    style="width: 100%;height: 43px;font-weight: 600; font-style: normal;text-align: center;font-size: 30px;">
                    <tr>
                        @if(!empty($parcelNumber))
                            @foreach($parcelNumber as $d)
                                <td style="width: 52px; height: 52px; border: 1px solid black">{{ $d }}</td>
                            @endforeach
                        @else
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                            <td style="width: 52px; height: 52px; border: 1px solid black"></td>
                        @endif
                    </tr>
                </table>
            </td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6724620></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:15.0pt'>
            <td height=20 class=xl6324620 style='height:15.0pt'></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl7024620></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:15.0pt'>
            <td height=20 class=xl6324620 style='height:15.0pt'></td>
            <td colspan=18 rowspan=2 class=xl12024620 width=551 style='width:414pt'>IMDG-Note/
                Multimodal Dangerous Goods Note
            </td>
            <td class=xl6424620></td>

        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=18 rowspan=2 class=xl12124620 width=551 style='width:414pt'>IMO-Erkl&auml;rung
                f&uuml;r den Transport gef&auml;hrlicher G&uuml;ter
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:16.5pt'>
            <td height=22 class=xl6324620 style='height:16.5pt'></td>
            <td colspan=12 class=xl14024620 style='border-right:.5pt solid black'><font
                    class="font724620">Shipper</font><font class="font824620"> </font><font
                    class="font524620">(Versender)</font></td>
            <td colspan=6 class=xl14024620 style='border-right:.5pt solid black;border-left:none'>
                <font class="font724620">Receiver</font><font
                    class="font824620"> </font>
                <font class="font524620">(Empf&auml;nger)</font>
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:12.0pt'>
            <td height=16 class=xl6324620 style='height:12.0pt'></td>
            <td colspan=12 class=xl14324620 style='border-right:.5pt solid black'>
                {{ $senderData['company_name'] ?? '' }}
            </td>
            <td colspan=6 class=xl10624620 style='border-right:.5pt solid black;border-left:none'>
                {{ $order->order_shipping_address->name ?? '' }} {{ $order->order_shipping_address->last_name ?? '' }}
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:12.0pt'>
            <td height=16 class=xl6324620 style='height:12.0pt'></td>
            <td colspan=12 class=xl14324620 style='border-right:.5pt solid black'>
                {{ $senderData['address_1'] ?? '' }}  {{ $senderData['address_2'] ?? '' }}
            </td>
            <td colspan=6 class=xl10624620 style='border-right:.5pt solid black;border-left:none'>
                {{ $order->order_shipping_address->address ?? '' }} {{ $order->order_shipping_address->address_2 ?? '' }}
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:12.0pt'>
            <td height=16 class=xl6324620 style='height:12.0pt'></td>
            <td colspan=12 class=xl14324620 style='border-right:.5pt solid black'>
                {{ $senderData['postal_code'] ?? '' }} {{ $senderData['city'] ?? '' }}
            </td>
            <td colspan=6 class=xl10624620 style='border-right:.5pt solid black;border-left:none'>
                {{ $order->order_shipping_address->zip ?? '' }} {{ $order->order_shipping_address->city ?? '' }}
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:12.0pt'>
            <td height=16 class=xl6324620 style='height:12.0pt'></td>
            <td colspan=12 class=xl14324620 style='border-right:.5pt solid black'>
                {{ $senderData['country'] ?? '' }}
            </td>
            <td colspan=6 class=xl10624620 style='border-right:.5pt solid black;border-left:none'>
                {{ $order->order_shipping_address->country_code ?? '' }}
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style="mso-height-source:userset;height:15.0pt;">
            <td class="xl6324620" style="height:15.0pt;"></td>
            <td colspan="3" rowspan="4" class="xl12224620" style="border-right:.5pt solid black; border-bottom:.5pt solid black;">
                UN-No.
            </td>
            <td colspan="12" rowspan="4" class="xl10924620" style="border-right:.5pt solid black; border-bottom:.5pt solid black; width:258pt;">
                <font class="font1224620">Proper-Shipping &amp; Technical (n.o.s.) Name where applicable,</font>
                <br><font class="font624620"></font>
                <font class="font1324620">(Versandbezeichnung und ggf. technischer -n.a.g. -Name)</font>
            </td>
            <td colspan="2" rowspan="4" class="xl13424620" style="border-right:.5pt solid black; border-bottom:.5pt solid black; width:62pt;">
                <font class="font1224620">Primary &amp; Subsidiary Hazard Class</font>
                <br><font class="font1324620">(Gefahrzettelnummer(n))</font>
            </td>
            <td rowspan="4" class="xl13124620" style="border-bottom:.5pt solid black; border-top:none; width:49pt;">
                <font class="font1224620">Packaging-Group</font>
                <br><font class="font1324620">(Verpackungsgruppe)</font>
            </td>
            <td class="xl6424620"></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        @if($unNumbers)
            @foreach($unNumbers as $number)
                <tr>
                    <td class=xl6324620></td>
                    <td colspan=3 class=xl9524620 style='border-right:.5pt solid black'>
                        {{ $number['un_numbers'] }}
                    </td>
                    <td colspan=12 class=xl10624620 style='border-right:.5pt solid black;border-left:none'>
{{--                        {{ $number['mark_nos'] }}--}}
                        {{ $number['proper_shipping_name'] }}
                    </td>
                    <td colspan=2 class=xl11824620 style='border-right:.5pt solid black;border-left:none'>
                        {{ $number['dangerous_goods_class'] }}
                    </td>
                    <td class=xl6524620 style='border-top:none;border-left:none'>
                        {{ $number['package_group'] }}
                    </td>
                    <td class=xl6424620></td>
                </tr>
            @endforeach
        @endif
        @if(count($unNumbers) < 5)
            @for($i = 1; $i <= 5 - count($unNumbers); $i++)
                <tr>
                    <td class="xl6324620" style="height:14.4pt"></td>
                    <td colspan="3" class="xl9524620" style="border-right:.5pt solid black"></td>
                    <td colspan="12" class="xl10624620" style="border-right:.5pt solid black;border-left:none"></td>
                    <td colspan="2" class="xl11824620" style="border-right:.5pt solid black;border-left:none"></td>
                    <td class="xl6524620" style="border-top:none;border-left:none"></td>
                    <td class="xl6424620"></td>
                </tr>
            @endfor
        @endif
        <tr>
            <td height="19" class="xl6324620" style="height:14.4pt"></td>
            <td colspan="6" rowspan="3" class="xl10924620"
                style="border-right:.5pt solid black; border-bottom:.5pt solid black; width:99pt;">
                <font class="font1224620">Number and kind of packages</font>
                <br><font class="font1324620">(Anzahl und Art der Verpackung)</font>
            </td>
            <td colspan="3" rowspan="3" class="xl10924620"
                style="border-right:.5pt solid black; border-bottom:.5pt solid black; width:54pt;">
                <font class="font1224620">Gross-Weight in Kg</font>
                <br><font class="font1324620">(Bruttogewicht)</font>
            </td>
            <td colspan="3" rowspan="3" class="xl10924620"
                style="border-right:.5pt solid black; border-bottom:.5pt solid black; width:61pt;">
                <font class="font1224620">Net-Weight Kg or L</font>
                <br><font class="font1324620">(Nettogewicht/-menge)</font>
            </td>
            <td colspan="6" rowspan="3" class="xl10924620"
                style="border-right:.5pt solid black; border-bottom:.5pt solid black; width:200pt;">
                <font class="font1224620">Properties where applicable e.g. Flashpoint, Marine Pollutant etc.</font>
                <br><font class="font1324620">(Eigenschaften wie zutreffend, z.B. Flammpunkt, seewassergefährdend
                    etc.)</font>
            </td>
            <td class="xl6424620"></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>

        @foreach($items as $itm )
            <tr style='height:14.4pt'>
                <td height=19 class=xl6324620 style='height:14.4pt'></td>
                <td colspan=6 class=xl9224620 style='border-right:.5pt solid black'>
                    {{ $itm['quantity'] }}x{{ $itm['name'] }}
                </td>
                <td colspan=3 class=xl9524620 style='border-right:.5pt solid black;border-left:none'>
                    {{ $itm['quantity'] * $itm['gross_weight'] }}
                </td>
                <td colspan=3 class=xl9524620 style='border-right:.5pt solid black;border-left:none'>
                    {{$itm['quantity'] * $itm['net_weight'] }}
                </td>
                <td colspan=6 class=xl9524620 style='border-right:.5pt solid black;border-left:none'>
                    {{ $itm['properties'] }}
                </td>
                <td class=xl6424620></td>
            </tr>
        @endforeach
        @if(count($items) < 5)
            @for($i = 1; $i <= 5 - count($items); $i++)
                <tr>
                    <td height=19 class=xl6324620 style='height:14.4pt'></td>
                    <td colspan=6 class=xl9224620 style='border-right:.5pt solid black'></td>
                    <td colspan=3 class=xl9524620 style='border-right:.5pt solid black;border-left:none'></td>
                    <td colspan=3 class=xl9524620 style='border-right:.5pt solid black;border-left:none'></td>
                    <td colspan=6 class=xl9524620 style='border-right:.5pt solid black;border-left:none'></td>
                    <td class=xl6424620></td>
                </tr>
            @endfor
        @endif
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=18 rowspan=2 height=44 width=551 style='height:33.15pt;text-align: center;width:414pt' align=center valign=top>
                <br>
                <strong style="color: black; font-size: 30px; font-weight: 700; font-style: normal; font-family: Tahoma, sans-serif;">1
                    fibreboard box</strong>
                <hr>
                <font class="font1724620">Please tick
                    the applicable box if limited or excepted quantities are contained</font>
                <br>
                <font
                    class="font1624620">(Bitte Zutreffendes ankreuzen, wenn begrenzte oder freigestellte Mengen enthalten sind).</font>
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr>
            <td height=25 class=xl6324620 style='height:18.75pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr>
            <td height=20 class=xl6324620 style='height:15.0pt'></td>
            <td colspan=2 class=xl9024620 style='border-right:1.0pt solid black'><span
                    style='mso-spacerun:yes'>&#9745;</span></td>
            <td colspan=16 class=xl9824620 style='border-right:.5pt solid black'>Dangerous
                Good in Limited Quantities, LTD. QTY. <font class="font1124620">G</font><font
                    class="font1024620">efahrgut in begrenzten Mengen</font></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=2 class=xl7924620 style='border-right:1.0pt solid black'><span
                    style='mso-spacerun:yes'></span></td>
            <td colspan=16 class=xl7624620 style='border-right:.5pt solid black'><font
                    class="font1424620">Dangerous goods in Excepted Quantities</font><font
                    class="font1124620"> </font><font class="font1024620">(Gefahrgut in
                    freigestellten Mengen)</font></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:3.0pt'>
            <td height=4 class=xl6324620 style='height:3.0pt'></td>
            <td colspan=18 class=xl8724620 width=551 style='border-right:.5pt solid black;width:414pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:15.0pt'>
            <td height=20 class=xl6324620 style='height:15.0pt'></td>
            <td colspan=18 rowspan=3 class=xl8124620 width=551 style='border-right:.5pt solid black;width:414pt'>
                <font class="font2024620">Please add quantities per
                    transport-category to get ADR points if not included in other
                    tansport-document. Not applicable for limited or excepted quantities.</font>
                <br>
                <font class="font1624620">
                    (Bitte Mengen pro Bef&ouml;rderungskategorie zum Berechnen der
                    ADR-Punkte eintragen, sofern dies nicht in einem anderen
                    Bef&ouml;rderungsdokument aufgef&uuml;hrt ist. Nicht notwendig f&uuml;r LQ-
                    oder EQ-Mengen)
                </font>
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:3.0pt'>
            <td height=4 class=xl6324620 style='height:3.0pt'></td>
            <td colspan=18 class=xl14824620 width=551 style='border-right:.5pt solid black;width:414pt'>
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:15.0pt'>
            <td height=20 class=xl6324620 style='height:15.0pt'></td>
            <td colspan=12 class=xl17024620>
                <font class="font924620">Transport-Category1:</font>
                <font class="font624620">(Bef&ouml;rderungskategorie 1)</font>
            </td>
            <td class=xl7324620 style='border-top:none'></td>
            <td class=xl7124620 style='border-top:none;border-left:none'>kg/l</td>
            <td class=xl7424620 align=right style='border-top:none;border-left:none'>0</td>
            <td class=xl7124620 style='border-top:none;border-left:none'>Points</td>
            <td colspan=2 rowspan=3 class=xl19324620 width=98 style='width:73pt'>
                <font class="font924620">Sum ADR-Points:</font>
                <br>
                <font class="font1324620">(Summe ADR-Punkte)</font>
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=12 class=xl17024620><font class="font924620">Transport-Category2:</font><font
                    class="font1124620"> </font><font class="font624620">(Bef&ouml;rderungskategorie
                    2)</font></td>
            <td class=xl7324620 style='border-top:none'></td>
            <td class=xl7124620 style='border-top:none;border-left:none'>kg/l</td>
            <td class=xl7424620 align=right style='border-top:none;border-left:none'>0</td>
            <td class=xl7124620 style='border-top:none;border-left:none'>Points</td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=12 class=xl17024620><font class="font924620">Transport-Category3:</font><font
                    class="font1124620"> </font><font class="font624620">(Bef&ouml;rderungskategorie
                    3)</font></td>
            <td class=xl7324620 style='border-top:none'></td>
            <td class=xl7124620 style='border-top:none;border-left:none'>kg/l</td>
            <td class=xl7424620 align=right style='border-top:none;border-left:none'>0</td>
            <td class=xl7124620 style='border-top:none;border-left:none'>Points</td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=12 class=xl19124620 style='border-right:.5pt solid black'><font
                    class="font924620">Transport-Category4:</font><font class="font1124620"> </font><font
                    class="font624620">(Bef&ouml;rderungskategorie 4)</font></td>
            <td class=xl7324620 style='border-top:none;border-left:none'></td>
            <td class=xl7224620 style='border-top:none;border-left:none'>kg/l</td>
            <td class=xl7524620 align=right style='border-top:none;border-left:none'>0</td>
            <td class=xl7224620 style='border-top:none;border-left:none'>Points</td>
            <td colspan=2 class=xl19724620 width=98 style='width:73pt'>0</td>
            <td class=xl6424620></td>
        </tr>
        <tr style='mso-height-source:userset;height:2.25pt'>
            <td height=2 class=xl6324620 style='height:2.25pt'></td>
            <td colspan=18 class=xl15124620 style='border-right:.5pt solid black'></td>
            <td class=xl6424620></td>


        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=12 rowspan=4 class=xl16124620 width=285 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:214pt'>
                <font class="font2124620">Declaration</font>
                <font class="font1324620">:I hereby declare that the contents of this consignment
                    are fully and accurately described by the proper shipping-name(s) and are
                    classified, marked, labelled and in all respects in proper condition for
                    transport according to the applicable international and national governmental
                    regulations</font>
            </td>
            <td colspan=6 rowspan=3 class=xl17124620 width=266 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:200pt'>Name/position,
                Company/Organisation of Signatory
                <br>
                <font
                    class="font624620">(Name/Funktion, Unternehmen/Organisation des
                    Unterzeichners</font></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=6 rowspan=2 class=xl18024620 width=266 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:200pt'>Prokurist EPODEX GmbH
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=12 rowspan=5 class=xl16124620 width=285 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:214pt'><font class="font2124620">Erkl&auml;rung:</font><font
                    class="font1324620"> Hiermit erkl&auml;re ich, da&szlig; der Inhalt der
                    Sendung mit dem/ den richtigen technischen Namen vollst&auml;ndig und genau
                    beschrieben ist/sind. Die G&uuml;ter sind nach den geltenden internationalen
                    Regelungen klassifiziert, verpackt, beschriftet und markiert und befinden
                    sich in jeder Hinsicht in einem f&uuml;r die Bef&ouml;rderung geeigneten
                    Zustand.</font></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=3 rowspan=2 class=xl17124620 width=118 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:89pt'>Place and Date <font
                    class="font624620">(Ort und Datum)</font></td>
            <td colspan=3 rowspan=2 class=xl17124620 width=148 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:111pt'>Signature<span
                    style='mso-spacerun:yes'> </span><font class="font624620">(Unterschrift)</font></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td colspan=3 rowspan=2 class=xl15424620 style='border-right:.5pt solid black;border-bottom:.5pt solid black;width:89pt'>
                {{ $senderData['city'] }} {{ date( 'd.m.Y' ) }}
            </td>
            <td colspan=3 rowspan=2 class=xl16024620 style='border-right:.5pt solid black;border-bottom:.5pt solid black'>
                <img style="width:280px; height:auto" width="280"
                     src="data:image/jpeg;base64,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"/>
            </td>
            <td class=xl6424620></td>
        </tr>
        <tr style='height:14.4pt'>
            <td height=19 class=xl6324620 style='height:14.4pt'></td>
            <td class=xl6424620></td>
        </tr>
        <tr style='display:none'>
            <td width=38 style='width:29pt'></td>
            <td width=12 style='width:9pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=24 style='width:18pt'></td>
            <td width=26 style='width:20pt'></td>
            <td width=25 style='width:19pt'></td>
            <td width=30 style='width:22pt'></td>
            <td width=32 style='width:24pt'></td>
            <td width=33 style='width:25pt'></td>
            <td width=53 style='width:40pt'></td>
            <td width=50 style='width:38pt'></td>
            <td width=32 style='width:24pt'></td>
            <td width=66 style='width:49pt'></td>
            <td width=38 style='width:29pt'></td>
            <td width=83 style='width:62pt'></td>
            <td width=83 style='width:62pt'></td>
            <td width=83 style='width:62pt'></td>
            <td width=83 style='width:62pt'></td>
        </tr>
    </table>

    <div style="font-size: 12px; padding: 40px 160px 0 0;">
        <strong>5.4.1.4.3 Ergänzungen zum richtigen technischen Namen in den Angaben über die gefährlichen
            Güter</strong>
        <br>
        <span>Der richtige technische Name (siehe 3.1.2) in den Angaben über die gefährlichen Güter muss wie folgt
            ergänzt
            werden:</span>
        <br><br>
        <span>Flammpunkt: Wenn die zu befördernden flüssigen gefährlichen Güter eine Haupt- oder
            Zusatzgefahr der
            Klasse 3 und einen Flammpunkt von 60 °C oder darunter (in °C geschlossener Tiegel (c.c.)) aufweisen,
            muss der niedrigste Flammpunkt angegeben werden. Sind Verunreinigungen vorhanden, kann der
            Flammpunkt höher oder niedriger sein als die in der Gefahrgutliste für den Stoff angegebene
            Bezugstemperatur.</span>
    </div>
</div>
</body>
