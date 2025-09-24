<?php 
    include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/catalogos/catalogosDependencias_class.php");
    if(isset($_GET['id'])){
        $depe_id_base64 = $_GET['id'];
        $depe_id = base64_decode($depe_id_base64);
        
        $dependencia=new dependencia_SQLlista();
        $dependencia->whereID($depe_id);
        $dependencia->setDatos();
        $depe_nombre=$dependencia->field('depe_nombre');
        $depe_logo=$dependencia->field('file_logo');
        $depe_logo_path='../docs/catalogos/'.SIS_EMPRESA_RUC.'/'.$depe_id.'/'.$depe_logo;

    }else{
        $depe_nombre=SIS_EMPRESA;
        $depe_logo="logo_".strtolower(SIS_EMPRESA_SIGLAS).".png";
        $depe_logo_path="assets/images/".$depe_logo;
    }
 
    if(!file_exists($depe_logo_path) || !$depe_logo){
        $depe_logo_path="";
    }

?>

<!-- Page header -->
	<div class="page-header page-header-inverse bg-indigo">

		<!-- Main navbar -->
		<div class="navbar navbar-inverse navbar-transparent">
			<div class="navbar-header">
                            <a class="navbar-brand" href="index.php">
                                <span><?php if ($depe_logo_path!="") echo "<img src=\"$depe_logo_path\" width=\"30\" height=\"30\" alt=\"\">"?></span>
                                <?php echo $depe_nombre; ?>
                                </a>
				<ul class="nav navbar-nav pull-right visible-xs-block">
					<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-grid3"></i></a></li>
				</ul>
			</div>
			
                        <div class="navbar-collapse collapse" id="navbar-mobile">
				<ul class="nav navbar-nav">
					<!--li><a href="consultas.php" target="_blank"><i class="glyphicon glyphicon-search"></i> Consulte su Solicitud</a></li-->
                                        <li><a href="/sisadmin/gestdoc/index.php" target="_blank"><i class="glyphicon glyphicon-search"></i> Consulte su Solicitud</a></li>
                                        <li><a href="/sisadmin/intranet/modulos/index.php" target="_blank"><i class="glyphicon glyphicon-log-in"></i> Sistema Documentario</a></li>
                                        <li><a href="javascript:void(0);" data-href="mensaje.html" class="openPopup"><b><i class="glyphicon glyphicon-question-sign text-white"></i></b>Ayuda</a></li>                                        
				</ul>
			</div>
		</div>
		<!-- /main navbar -->


		<!-- Page header content -->
		<div class="page-header-content">
			<div class="page-title">
                            <h4>Mesa de Partes Virtual <small>Registre su Solicitud</small></h4>
                                
			</div>
                    
			<div class="heading-elements">
				<ul class="breadcrumb heading-text">
					<li><a href="index.php"><i class="icon-home2 position-left"></i> Inicio </a></li>
					<li class="active">Mesa de Partes Virtual</li>
				</ul>
			</div>
		</div>
		<!-- /page header content -->

	</div>
	<!-- /page header -->