<?php
 /*
 Copyright 2000, 2001, 2002, 2003, 2004, 2005 Dataprev - Empresa de Tecnologia e Informa��es da Previd�ncia Social, Brasil

 Este arquivo � parte do programa CACIC - Configurador Autom�tico e Coletor de Informa��es Computacionais

 O CACIC � um software livre; voc� pode redistribui-lo e/ou modifica-lo dentro dos termos da Licen�a P�blica Geral GNU como
 publicada pela Funda��o do Software Livre (FSF); na vers�o 2 da Licen�a, ou (na sua opni�o) qualquer vers�o.

 Este programa � distribuido na esperan�a que possa ser  util, mas SEM NENHUMA GARANTIA; sem uma garantia implicita de ADEQUA��O a qualquer
 MERCADO ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU para maiores detalhes.

 Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo "LICENCA.txt", junto com este programa, se n�o, escreva para a Funda��o do Software
 Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
  /*********************************************/
  /*  PHP TreeMenu 1.1                         */
  /*                                           */
  /*  Author: Bjorge Dijkstra                  */
  /*  email : bjorge@gmx.net                   */
  /*                                           */
  /*  Placed in Public Domain                  */
  /*                                           */
  /*********************************************/

  /*********************************************/
  /*  Settings                                 */
  /*********************************************/
  /*                                           */
  /*  $treefile variable needs to be set in    */
  /*  main file                                */
  /*                                           */
  /*********************************************/

  if(isset($PATH_INFO)) {
	  $script       =  $PATH_INFO;
  } else {
	  $script	=  $SCRIPT_NAME;
  }

  $img_expand   = "/sisadmin/intranet/img/tree_expand.gif";
  $img_collapse = "/sisadmin/intranet/img/tree_collapse.gif";
  $img_line     = "/sisadmin/intranet/img/tree_vertline.gif";
  $img_split	= "/sisadmin/intranet/img/tree_split.gif";
  $img_end      = "/sisadmin/intranet/img/tree_end.gif";
  $img_leaf     = "/sisadmin/intranet/img/tree_leaf.gif";
  $img_spc      = "/sisadmin/intranet/img/tree_space_menu_esq.gif";
  $img_begin	= "/sisadmin/intranet/img/tree_begin.gif";

  /*********************************************/
  /*  Read text file with tree structure       */
  /*********************************************/

  /*********************************************/
  /* read file to $tree array                  */
  /* tree[x][0] -> tree level                  */
  /* tree[x][1] -> item text                   */
  /* tree[x][2] -> item link                   */
  /* tree[x][3] -> link target                 */
  /* tree[x][4] -> item image         NEW      */
  /* tree[x][5] -> item title         NEW      */
  /* tree[x][6] -> show item ?        NEW      */
  /* tree[x][7] -> last item in subtree        */
  /*********************************************/

  $maxlevel=0;
  $cnt=0;

  while ($rs->getrow())
  {
    $tree[$cnt][0]=strspn($rs->field("tree_text"),".");
	$tmp=rtrim(substr($rs->field("tree_text"),$tree[$cnt][0]));
    $node=explode("|",$tmp);

    $tree[$cnt][1]=$node[0];
    $tree[$cnt][2]=$node[1];
    $tree[$cnt][3]=$node[2];
	$tree[$cnt][4]=$node[3];
	$tree[$cnt][5]=$node[4];
	$tree[$cnt][6]=$node[5];
    $tree[$cnt][7]=0;
	$tree[$cnt][8]=$rs->field("tree_id");
	$tree[$cnt][9]=$rs->field("tree_logico");


    if ($tree[$cnt][0] > $maxlevel) $maxlevel=$tree[$cnt][0];
    $cnt++;
  }

  for ($i=0; $i<count($tree); $i++) {
     $expand[$i] = $tree_expand;   //0 por defecto;
     $visible[$i]= $tree_visible; //0 por defecto;
     $levels[$i]=0;
  }

  /*********************************************/
  /*  Get Node numbers to expand               */
  /*********************************************/

//  if ($_GET['p']!="") $explevels = explode("|",$_GET['p']);

  if ($_GET['p']!="") $explevels = explode("|",str_replace("*","#",$_GET['p']));

  $i=0;
  while($i<count($explevels))
  {
    $expand[$explevels[$i]]=1;
    $i++;
  }


  /*********************************************/
  /*  Permito apenas uma expans�o              */
  /*********************************************/

  $i=0;
  while($i<count($tree))
  {
  	if ($tree[$i][0] == 1 and $_GET['v_no'])
		{
		if ($_GET['v_no'] <> $i )
			{
		    $expand[$i]=0;
			}
		}
    $i++;
  }


  /*********************************************/
  /*  Find last nodes of subtrees              */
  /*********************************************/

  $lastlevel=$maxlevel;
  for ($i=count($tree)-1; $i>=0; $i--)
  {
     if ( $tree[$i][0] < $lastlevel )
     {
       for ($j=$tree[$i][0]+1; $j <= $maxlevel; $j++)
       {
          $levels[$j]=0;
       }
     }
     if ( $levels[$tree[$i][0]]==0 )
     {
       $levels[$tree[$i][0]]=1;
       $tree[$i][7]=1;
     }
     else
       $tree[$i][7]=0;
     $lastlevel=$tree[$i][0];
  }


  /*********************************************/
  /*  Determine visible nodes                  */
  /*********************************************/

// all root nodes are always visible
  for ($i=0; $i < count($tree); $i++) if ($tree[$i][0]==1) $visible[$i]=1;


  for ($i=0; $i < count($explevels); $i++)
  {
    $n=$explevels[$i];
    if ( ($visible[$n]==1) && ($expand[$n]==1) )
    {
       $j=$n+1;
       while ( $tree[$j][0] > $tree[$n][0] )
       {
         if ($tree[$j][0]==$tree[$n][0]+1) $visible[$j]=1;
         $j++;
       }
    }
  }


  /*********************************************/
  /*  Output nicely formatted tree             */
  /*********************************************/

  for ($i=0; $i<$maxlevel; $i++) $levels[$i]=1;

  $maxlevel++;

  echo "<table id=\"tLista\" style=\"font-family : Verdana,Arial; font-size : 8pt\" cellspacing=0 cellpadding=0 border=0 cols=".($maxlevel+3)." width=100%>\n";
  echo "<tr>";
  for ($i=1; $i<$maxlevel*2; $i++) echo "<td class=\"menu_tree_td\" width=16></td>";
  echo "<td class=\"menu_tree_td\" width=100%>&nbsp;</td></tr>\n";
  $cnt=0;
  while ($cnt<count($tree))
  {
    if ($tree[$cnt][6])
		{
		$ShowTheNode=0;
		}
	else
		{
		$ShowTheNode=1;
		}

    if ($visible[$cnt] and $ShowTheNode)
    {
      /****************************************/
      /* start new row                        */
      /****************************************/
      echo "<tr nowrap>";

      /****************************************/
      /* vertical lines from higher levels    */
      /****************************************/
      $i=1;
      while ($i<$tree[$cnt][0]-1)
      {
        if ($levels[$i]==1)
            echo "<td class=\"menu_tree_td\" colspan=".iif($tree[0][4],"!=","","2","1")." nowrap><a name='$cnt'></a><img src=\"".$img_line."\"></td>";
        else
            echo "<td class=\"menu_tree_td\" colspan=".iif($tree[0][4],"!=","","2","1")." nowrap><a name='$cnt'></a><img src=\"".$img_spc."\"></td>";
        $i++;
      }


      /****************************************/
      /* corner at end of subtree or t-split  */
      /****************************************/
  	if ($cnt == 0){}
	else
      if ($tree[$cnt][7]==1)
      {
        echo "<td class=\"menu_tree_td\" nowrap><a name='$cnt'><img src=\"".$img_end."\"></td>";
        $levels[$tree[$cnt][0]-1]=0;
      }
      else
      {
	  	$levels[$tree[$cnt][0]-1]=1;
	  	if ($cnt == 0)
		{
			echo "<td class=\"menu_tree_td\" nowrap><a name='$cnt'><img src=\"".$img_begin."\"></td>";
		}
		else
		{
        	echo "<td class=\"menu_tree_td\" nowrap><a name='$cnt'><img src=\"".$img_split."\"></td>";
        }
      }

      /********************************************/
      /* Node (with subtree) or Leaf (no subtree) */
      /********************************************/
      if ($tree[$cnt+1][0]>$tree[$cnt][0])
      {

        /****************************************/
        /* Create expand/collapse parameters    */
        /****************************************/
        $i=0;
		$pos=strpos($_SERVER['QUERY_STRING'],"p=");
		$pos=$pos?$pos-1:0;
		$UrlString=substr($_SERVER['QUERY_STRING'],0,$pos);
		if(strlen($UrlString)>0)
			$params="?".$UrlString."&p=";
		else
			$params="?p=";

        while($i<count($expand))
        {
          if ( ($expand[$i]==1) && ($cnt!=$i) || ($expand[$i]==0 && $cnt==$i))
          {
            $params=$params.$i;
            $params=$params."|";
          }
          $i++;
        }
		if ($tree[$cnt][0]==1) $params.="&v_no=".$cnt;
//        echo "<td nowrap>";
        if ($expand[$cnt]==0)
			{
            echo "<td class=\"menu_tree_td\" nowrap><a class=\"menu_tree_td\" href=\"".$script.$params."#$cnt\"><img src=\"".$img_expand."\" border=no></a></td>";
//          echo "<a href=\"".$script.$params."#$cnt\"><img src=\"".$img_expand."\" border=no></a>";
//			echo "<a href=\"".$script.$params."#$cnt\"><img src=\"".$tree[$cnt][4]."\" border=no></a>";
			}
        else
			{
            echo "<td class=\"menu_tree_td\" nowrap><a class=\"menu_tree_td\" href=\"".$script.$params."#$cnt\"><img src=\"".$img_collapse."\" border=no></a></td>";
//            echo "<a href=\"".$script.$params."#$cnt\"><img src=\"".$img_collapse."\" border=no></a>";
//            echo "<a href=\"".$script.$params."#$cnt\"><img src=\"".$tree[$cnt][4]."\" border=no></a>";
			}
//		echo "</td>";
        if ($tree[$cnt][4])
			echo "<td class=\"menu_tree_td\" nowrap><img src=\"".$tree[$cnt][4]."\" border=no></td>";

      }
      else
      {
        /*************************/
        /* Tree Leaf             */
        /*************************/
        if ($tree[$cnt][4])
        	{
			echo "<td class=\"menu_tree_td\" nowrap><img src=\"".$tree[$cnt][4]."\"></td>";
			}
		else
			{
        	echo "<td class=\"menu_tree_td\" nowrap><img src=\"".$img_leaf."\"></td>";
			}
      }

      /****************************************/
      /* output item text                     */
      /****************************************/
      if ($tree[$cnt][2]=="") //si NO tiene link
//          echo "<td colspan=".($maxlevel-$tree[$cnt][0]).">".$tree[$cnt][1]."</td>";
		  if($deletedTree && $tree[$cnt][8])
	          echo "<td class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" nowrap colspan=".($maxlevel*2)."><input type=\"checkbox\" name=\"sel[]\" value=\"".$tree[$cnt][8]."\">".$tree[$cnt][1]."</td>";
		  else
	          echo "<td class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" nowrap colspan=".($maxlevel*2)."><font style=\"background-color: #FFFFFF; font-weight: bold; font-size: 8px; color: ".iif($tree[$cnt][9],'==','f','red','black')."; font-family: Verdana, Arial, sans;\" >".$tree[$cnt][1]."</font></td>";
      else
		  if($deletedTree && $tree[$cnt][8])
	          echo "<td class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" nowrap colspan=".($maxlevel*2)."><input type=\"checkbox\" name=\"sel[]\" value=\"".$tree[$cnt][8]."\"><a class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" href=\"".$tree[$cnt][2]."\" target=\"".$tree[$cnt][3]."\" title=\"".$tree[$cnt][5]."\">".$tree[$cnt][1]."</a></td>";
		  else
 				if ($tree_selecOnlyEnd && $tree[$cnt+1][0]>$tree[$cnt][0]) //solo permite seleccionar los ultimos
          	  		echo "<td class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" nowrap colspan=".($maxlevel*2).">".$tree[$cnt][1]."</td>";
				else //si es el ultimo elemento
          	  		echo "<td class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" nowrap colspan=".($maxlevel*2)."><a class=\"".iif($tree[$cnt][9],'==','f','menu_tree_anulado_td','menu_tree_td')."\" href=\"".$tree[$cnt][2]."\" target=\"".$tree[$cnt][3]."\" title=\"".$tree[$cnt][5]."\">".$tree[$cnt][1]."</a></td>";
      /****************************************/
      /* end row                              */
      /****************************************/

      echo "</tr>\n";
    }
    $cnt++;
  }
  echo "</table>\n";


