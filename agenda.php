<?php
/**
* Projeto de aplicação CRUD ultilizando PDO - Agenda de Contatos
* 
*/

//Verificar se foii enviando dados via POST
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = (isset($_POST['id']) && $_POST['id'] != null) ? $_POST['id'] : "";
    $nome =(isset($_POST['nome']) && $_POST['nome'] != null) ? $_POST['nome'] : "";
    $email = (isset($_POST['email']) && $_POST['email'] != null) ? $_POST['email'] : "";
    $celular = (isset($_POST['celular']) && $_POST['celular'] != null) ? $_POST['celular'] : "";
}else if(!isset($id)){
    //Se não foi setado nenhum valor para variável $id
    $id = (isset($_GET['id']) && $_GET['id'] != null) ? $_GET['id'] : "";
    $nome = NULL;
    $email = NULL;
    $celular = NULL;
}

//Criar a conexão com o banco de dados 
try{
    $conexao = new PDO("mysql:host=localhost;dbname=crudsimples","root","");
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->exec("set names utf8");
}catch(PDOException $erro){
    echo "Erro na conexão:".$erro->getMessage();
}

//Bloco if que  salva os dados no banco - atua como create e update.
if(isset($_REQUEST["act"]) && $_REQUEST["act"] == "save" && $nome != ""){
    try{
        if($id != ""){
            $stmt = $conexao->prepare("UPDATE contato SET nome=?, email=?, celular=? WHERE id=?");
            $stmt->bindParam(4,$id);
        }else{
            $stmt = $conexao->prepare("INSERT INTO contato (nome, email, celular) VALUES (?,?,?)");
        }
        $stmt->bindParam(1, $nome);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $celular);

        if($stmt->execute()){
            if($stmt->rowCount() > 0){
                echo "<span style='color:green;font-size: 20px;font-variant:bold;'>Dados Cadastrados com sucesso</span>";
                $id = NULL;
                $nome = NULL;
                $email = NULL;
                $celular = NULL;
            }else{
                echo "Erro ao tentar efetuar o cadastro";
            }
        }else{
            throw new PDOException("erro: Não foi possível executar a declaração SQL");
        }
    }catch(PDOException $erro){
        echo "Erro: ".$erro->getMessage();
    }
}

//Bloco if que recuprea as informações no formulário, etapa ultilizada pelo Update.
if(isset($_REQUEST["act"]) && $_REQUEST["act"] == "upd" && $id != ""){
    try{
        $stmt = $conexao->prepare("SELECT * FROM contato WHERE ID = ?");
        $stmt->bindParam(1,$id, PDO::PARAM_INT);
        if($stmt->execute()){
            $rs = $stmt->fetch(PDO::FETCH_OBJ);
            $id = $rs->id;
            $nome = $rs->nome;
            $email = $rs->email;
            $celular = $rs->celular;
        }else{
            throw new PDOException("Erro: Não foi possível executar a declaração SQL");
        }
    }catch(PDOException $erro){
            echo "Erro: ".$erro->getMessage();
    }
}

//Bloco if ultilizado pela etapa delete
if(isset($_REQUEST["act"]) && $_REQUEST["act"] == "del" && $id != ""){
    try{
        $stmt = $conexao->prepare("DELETE FROM contato WHERE id = ?");
        $stmt->bindParam(1,$id,PDO::PARAM_INT);
        if($stmt->execute()){
            echo "<span style='color:green;font-size: 20px;font-variant:bold;'>Registro foi excluido com êxito</span>";
            $id = null;
        }else{
            throw new PDOException("Erro: Não foi possível executar a declaração SQL");
        }
     }catch(PDOException $erro){
        echo "Erro: ".$erro->getMessage();
     }
}
?>
<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de contatos</title>
    <style>
        h1{
            font-size:50px;
            text-align: center;
        }
        input[type=text],[type=email]{
            padding:10px;
            font-size: 15px;
        }
        input[type=submit]{
            padding:10px;
            font-size: 15px;
            background-color:green;
            border: none;
            cursor: pointer;
        }
        input[type=reset]{
            padding:10px;
            font-size: 15px;
            background-color:red;
            border: none;
            cursor: pointer;
        }
        label{
            font-size:30px;
            padding:10px;
        }
        svg.f{
            display:inline-box;
            font-size: inherit;
            height: 1em;
        }

    </style>
</head>
<body>
    <form action="?act=save" method="POST" name="form1" >
        <h1>Agenda de contatos</h1>
        <hr>
        <input type="hidden" name="id" <?php
        
        //Preenche o id no campo id com um valor "value.
        if(isset($id) && $id != null || $id != ""){
            echo "value=\"{$id}\"";
        }
        ?>/>
        <label>Nome:</label>
        <input type="text" name="nome" <?php

        //Preenche o nome no campo nome com um valor "value.
        if(isset($nome) && $nome != null || $nome != ""){
            echo "value=\"{$nome}\"";
        }
        ?>/>

        <label>Email:</label>
        <input type="email" name="email" <?php

        //Preenche o email no campo email com um valor "value.
        if(isset($email) && $email != null || $email != ""){
            echo "value=\"{$email}\"";
        }?>/>

        <label>Celular:</label>
        <input type="text" name="celular" <?php

        //Preenche o celular no campo celular com um valor "value.
        if(isset($celular) && $celular != null || $celular != ""){
            echo "value=\"{$celular}\"";
        }
        ?>/>
        
        <input type="submit" value="Salvar">
        <input type="reset" value="Novo">
        <hr>
    </form>
    <table border="1" width="100%">
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Celular</th>
            <th>Ações</th>
        </tr>
        <?php

        //bloco que realiza o papel do read - recupera os dados e apresenta na tela
        try{
            $stmt = $conexao->prepare("SELECT * FROM contato");
            if($stmt->execute()){
                while($rs = $stmt->fetch(PDO::FETCH_OBJ)){
                    echo "<tr>";
                    echo "<td>".$rs->nome."</td><td>".$rs->email."</td><td>".$rs->celular
                                ."</td><td><center><a href=\"?act=upd&id=".$rs->id."\"><svg class='f' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='pencil-alt' class='svg-inline--fa fa-pencil-alt fa-w-16' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path fill='currentColor' d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg></i></a>"
                                ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
                                ."<a href=\"?act=del&id=".$rs->id."\"><svg class='f' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='trash-alt' class='svg-inline--fa fa-trash-alt fa-w-14' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path fill='currentColor' d='M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z'></path></svg></a></center></td>";
                    echo "</tr>";
                }
            }else{
                echo "Erro: Não foi possível recuperar os dados do banco de dados";
            }
        }catch(PDOException $erro){
            echo "Erro: ".$erro->getMessage();
        }
        ?>
    </table>
</body>
</html>