<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Grid Sistemas</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">	
	<link rel="stylesheet" href="css/style.css"> 
    <style>
        .borda-arredondada {
            width: 200px;
            height: 70px;
            background-color: #ffffff; /* Cor de fundo da div */           
            border-radius: 30px; /* Define um raio para as bordas, criando um efeito arredondado */
            text-align: center;
            color: #fff; /* Cor do texto dentro da div */
            line-height: 200px; /* Centraliza verticalmente o texto */
        }
    </style>
</head>
<body>	
	
    <div style="z-index: 2; position: relative; top: 3px; left: 1%;" class="borda-arredondada"><img style="position: absolute; top: 10px; left: 10px;" src="grid.png" width="180"></div>
    <table style="border-bottom: 1px solid gray; position: absolute; top:16px; right:30px; width:90%;"><tr><td align=right>Olá usuário</td></tr></table>
    <br><br><br><br>
	<div class="container">			
		<div class="login-wrap p-4 p-md-5">			
			<table align=center><tr><td height=200 valign=center align=center>
				<a href='../grid-psp'<br><img src="logoPsp.png" width="280"><br><font color=#006600 size=4>Programação para o Serviço de Pioneiro</font></a>
			</td><tr><tr><td border height=200 width=350 align=center>
				<a href='../grid-cdc'<br><img src="logoCdc.png" width="280"><br><font color=#993399 size=4>Controle de Designações Congregacionais</font></a>
			</td></tr></table><br>
		</div>
		<center><b>&copy; GRID Sistemas - 
		<? 
			if(date('Y')==2023) echo date('Y');
			else echo "2023 - ".date('Y');
		?>
		- Todos os direitos reservados</b>
	</div>	    
</body>
</html>
