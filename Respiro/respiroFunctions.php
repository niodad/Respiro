<?php

$_SESSION['days']  = array("Alle", "Maandag", "Dinsdag","Woensdag", "Donderdag", "Vrijdag", "Zaterdag", "Zondag") ;

function DutchName($day)

{

	switch($day)

	{

		case 1:

            $day = "maandag";

            break;

		case 2:

            $day = "dinsdag";

            break;

		case 3:

            $day = "woensdag";

            break;

		case 4:

            $day = "donderdag";

            break;

		case 5:

            $day = "vrijdag";

            break;

		case 6:

            $day = "zaterdag";

            break;

		case 7:

            $day = "zondag";

            break;

		case 0:

            $day = "alle dagen";

            break;

		default:

            $day = "";

            break;

	}

	return $day;

}

// convert the date

function ConvertDate($stamp)

{

    preg_match( "#/Date\((\d{10})\d{3}(.*?)\)/#", $stamp, $match );

    return date( "d-m-Y G:i", $match[1] );

}

function ConvertToTime($stamp)

{

    preg_match( "#/Date\((\d{10})\d{3}(.*?)\)/#", $stamp, $match );

    return date( "G:i", $match[1] );

}

function FilterLessons($lessons,$day,$sort)

{

    $SelectedLessons = array();

    foreach($lessons as $lesson)

    {

        if (($lesson->Day == $day || 'Alle' == $day)

            && (trim($lesson->Title) == trim($sort) || 'Alle' == $sort))

        {

            array_push($SelectedLessons,$lesson);

        }

    }

    return $SelectedLessons;

}

function addDay($lessons,$day ='Alle',$sort='Alle'){

    $returnstring='';

    $SelectedLessons = FilterLessons($lessons,$day,$sort);

    foreach($_SESSION['days'] as $day2)

    {

        $returnstring .= ShowLessonsByDay($SelectedLessons,$day2);

    }
    if( strlen($returnstring)==0){

        $returnstring .= 'Geen lessen gevonden voor soort: '.$sort.' op  dag: '.$day;

    }
    return $returnstring;

}

function ShowLessonsByDay($lessons,$day){

    $returnstring='';

    $DayLessons = array();

    foreach($lessons as $lesson)

    {

        if ($lesson->Day == $day)

        {

            array_push($DayLessons,$lesson);

        }

    }

    if (count($DayLessons)>0)

    {

        $returnstring .= '<button class="accordion">'.$day.'</button>

                <table class= "table-striped">';

        foreach ($DayLessons as $lesson)

        {

            $link = urlencode("$lesson->Day$lesson->Title$lesson->StartTime");

            $returnstring .=  '

            <tr>

                <td class="tijd"><a href="'.site_url().'/les?&link='.$link.'">'.$lesson->StartTime." - ".$lesson->EndTime.'</a></td>

                <td class="les"><a href="'.site_url().'/les?&link='.$link.'">'.$lesson->Title.'</a></td>

                <td class="zaal"><a href="'.site_url().'/les?&link='.$link.'">'.$lesson->Location.'</a></td>

                <td class="lesgever"><a href="'.site_url().'/les?&link='.$link.'">'.$lesson->Instructor.'</a></td>

				<td class="reserveer"><a href="http://respiro.be.nl.mysportspage.eu/" target="_blank">Reserveer</a></td>
            </tr>';

        }

        $returnstring .=  '</table>';

    }

    return $returnstring;

}

function GetLessonsFromRespiro()

{

    if(!isset($_SESSION['lessons']) && empty($_SESSION['lessons'])) {

        $lessonsUrl	=	'https://cms.concapps.nl/api/lessons?key=ccc9f858e72afa94ad1e03085cc328ce2fba835d&appid=967&branch=alll';

        $json	=	file_get_contents($lessonsUrl);

        $json_output	=	json_decode($json);

        $_SESSION['lessons'] = $json_output;

        usort($_SESSION['lessons'], function($a, $b) { //Sort the array using a user defined function

            return $a->StartTime > $b->StartTime ? 1 : -1; //Compare the scores

        });

    }

}

function GetTitlesFromLessons()

{

    $Categories = array();
    array_push($Categories,'Alle');

    foreach ($_SESSION['lessons'] as $value)

    {

        array_push($Categories,$value->Title );

    }
    sort($Categories);

    $Categories =array_unique($Categories);

    return $Categories ;

}

function createDropDown($list,$name,$labelName,$selected){

    $return_string ='';

    $return_string .= '

<div>

        <label for="sel'.$name.'">'.$labelName.'</label>

        <select class="form-control" id="select'.$name.'">';

    foreach ($list as $value)

    {
        if ($selected==$value)
        {
            $return_string .= '<option selected="selected">'.$value.'</option>';

        }else{
            $return_string .= '<option>'.$value.'</option>';

        }




    }

    $return_string .= '</select>
</div>

';

    return $return_string;

}

function GetLocationsFromLessons()

{

    $Locations = array();

    array_push($Locations,'Alle' );

    foreach ($_SESSION['lessons'] as $value)

    {

        array_push($Locations,$value->Location );

    }

    return array_unique($Locations);

}

function ShowRespiroLessons(){

    $return_string = '';
    $selectedDay='Alle';
    $selectedSort='Alle';

    if(isset($_GET['soort'] )) {

        $selectedSort= $_GET['soort'];

    }
    if(isset($_GET['dag'] )) {

        $selectedDay= $_GET['dag'];

    }

    GetLessonsFromRespiro();

    $Soorten = GetTitlesFromLessons();

    $return_string .= createDropDown($Soorten,'Sort','Soort',$selectedSort);

    $return_string .= createDropDown($_SESSION['days'] ,'Day','Dag',$selectedDay);

    $return_string .= '<div class="et_pb_button_module_wrapper et_pb_module">

			<br />	<a class="et_pb_button  et_pb_button_1 et_pb_module et_pb_bg_layout_light" onclick="SubmitFrm()" href="#">FILTER</a>
			</div>';

    $return_string .= '

        <div class="table-responsive">

            <table id="tableDays" class="table">

                <tr>';

    if (isset($_GET['dag'])) {

        $return_string .=  addDay($_SESSION['lessons'] ,$_GET['dag'],$_GET['soort']);

    }else{

        $return_string .= addDay($_SESSION['lessons']);

    }

    $return_string .= '
                </tr>
            </table>
            </div>

            <script type="text/javascript">

                function SubmitFrm() {

                    var sort = document.getElementById("selectSort").options[document.getElementById("selectSort").selectedIndex].text;

                    var day = document.getElementById("selectDay").options[document.getElementById("selectDay").selectedIndex].text;

                    window.location = "index.php?dag=" + day + "&soort=" + encodeURIComponent(sort);

                }

          </script>';

    return $return_string;

}

function ShowRespiroLesson(){

    $return_string = '';

    if(isset($_SESSION['lessons'])) {

        foreach($_SESSION['lessons'] as $lesson)

        {

            if(urldecode($_GET['link']) == $lesson->Day.$lesson->Title.$lesson->StartTime)

            {

                $lesson->Image = str_replace( 'http://', 'https://',$lesson->Image);


                $return_string .= <<<HTML

             <div class="lesson">

                <h2 class="ca-title">
                    {$lesson->Title}
                </h2>

                <h6><span style="text-transform: uppercase"> {$lesson->Day} </span>  {$lesson->StartTime} - {$lesson->EndTime}<br /> {$lesson->Location} </h6>

<div class="header-image" style="background: #eee url({$lesson->Image}) no-repeat center center ;">
                </div>

                <section>

                    <span>

                      <p>  {$lesson->Content}</p>
                    </span>
                </section>

                <br /><p><b>

                    Lesgever: {$lesson->Instructor}</b>
                </p>
            </div>

HTML;

            }

        }

	}

    return $return_string;

}

function GetNewsFromRespiro(){

    if(!isset($_SESSION['nieuws']) && empty($_SESSION['nieuws'])) {

        $lessonsUrl	=	'https://cms.concapps.nl/api/news?key=ccc9f858e72afa94ad1e03085cc328ce2fba835d&appid=967&branch=alll';

        $json	=	file_get_contents($lessonsUrl);

        $json_output	=	json_decode($json);

        $_SESSION['nieuws'] = $json_output;

        usort($_SESSION['nieuws'], function($a, $b) { //Sort the array using a user defined function

            return $a->DateUnixTimestamp > $b->DateUnixTimestamp ? -1 : 1; //Compare the scores

        });

    }

}
function site_url()
{
	return $_SERVER['DOCUMENT_ROOT'];
}


function CreateNewsNavigation($newsItems,$itemsToShow){

    $return_string = '<div class="nieuwspaginas">';

    $newsPages = ceil( $newsItems/$itemsToShow);

    for ($i = 0; $i < $newsPages; $i++) {

        if (isset($_GET['pagina'])) {

            if ($_GET['pagina']==$i)

            {

                if ($i==0)

                {

                    $return_string .= '<a class="active" href="'.site_url().'/nieuws/?&pagina='.$i.'"><b>'.($i + 1).'</b></a>' ;

                }else{

                    $return_string .= '<a class="active" href="'.site_url().'/nieuws/?&pagina='.$i.'"><b>'.($i + 1).'</b></a>' ;

                }

            }else{

                if ($i==0)

                {

                    $return_string .= '<a href="'.site_url().'/nieuws/?&pagina='.$i.'">'.($i + 1).'</a>' ;

                }else{

                    $return_string .= '<a href="'.site_url().'/nieuws/?&pagina='.$i.'">'.($i + 1).'</a>' ;

                }

            }

        }else{

            if ($i==0)

            {

            	$return_string .= '<a class="active" href="'.site_url().'/nieuws/?&pagina='.$i.'"><b>'.($i + 1).'</b></a>' ;

            }else{

                $return_string .= '<a href="'.site_url().'/nieuws/?&pagina='.$i.'">'.($i + 1).'</a>' ;

            }

        };

    }

    $return_string .= '</div>';

    return $return_string;

}

function ShowRespiroNews(){

    $return_string = '';

    GetNewsFromRespiro();

    $itemsToShow = 6;

    $start = 0;

    if (isset($_GET['pagina'])) {

        $start = $_GET['pagina'] * $itemsToShow;

    };

    $end = $start +  $itemsToShow;

    $newsItems = count($_SESSION['nieuws']);

    if ($newsItems<$end){

        $end =$newsItems;

    }

    $newsToShow = array_slice($_SESSION['nieuws'],$start, $itemsToShow );

    $return_string .= CreateNewsNavigation($newsItems,$itemsToShow);

    foreach($newsToShow  as $item)

    {

        $subTitle ='';

        if ($item->SubTitle != $item->Title)

        {

            $subTitle =  $item->SubTitle;

        }


            $item->Image = str_replace( 'http://', 'https://', $item->Image);


        $return_string .= <<<HTML

        <div class="blog-item mb30">

            <img class="img-responsive0" src="{$item->Image}" alt="{$item->Title}" title="{$item->Title}" />

            <div class="blog-item-content">

                <p class="end-item">

                    <span class="label label-info mr10">

                        {$item->Branch}
                    </span>
                </p>

                    <h2 class="blg-title">

                        {$item->Title}
                    </h2>

                <h3 class="blg-subtitle font-black-lt">

                    {$subTitle}
                </h3>

                <div class="blog-delen">

                    <p>

                        <span class="mr10"> <i class="fa fa-calendar"></i>{$item->Date}</span>|

                        <span class="ml5">

                            <i class="fa fa-pencil"></i>Respiro
                        </span>
                    </p>{$item->Content}
                </div>
            </div>
        </div>

HTML;

    }

    $return_string .= CreateNewsNavigation($newsItems,$itemsToShow);

    return $return_string;

}

function ShowRespiroNewsItem(){

    $return_string = '';

    foreach($_SESSION['nieuws'] as $news)

    {

        if($_GET['link'] == $news->Date)

        {

            $return_string .=<<<HTML

                <div class="newsitem">

                        <div class="blog-delen">

                            <h2 class="blg-title">

                                {$news->Title}
                            </h2>

                            <p>

                                <span class="mr5">

                                    <i class="fa fa-calendar"></i>{$news->Date}
                                </span>|

                                <span class="ml5">

                                    <i class="fa fa-pencil"></i>Respiro
                                </span>
                            </p>
                        </div>

                        <div class="video-image">

                            <div class="blog-imgliq">

                                <img class="img-responsive mr10 pull-left" src="{$news->Image}

                                            " alt="Hit Training" title="{$news->Title}" />
                            </div>
                        </div>

                        <div class="fulltekst">

                            {$news->Content}<div class="line-dotted"></div>
                        </div>
               </div>

HTML;

        }

    }

    return $return_string;

}



?>