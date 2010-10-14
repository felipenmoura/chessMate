<?php
    session_start();
    if(!isset($_SESSION['color']))
    {
    	$_SESSION['color']= 'white';
    	$_SESSION['alone']= true;
    }else{
	    $color= $_SESSION['color'];
	}
?><html>
    <head>
        <link type="text/css" rel="stylesheet" href="style.css" />
        <script src="../../client/jquery.js"></script>
    </head>
    <body onselectstart="return false" ondragstart="return false">
        <div id="background">
            <div id="table">
                <table>
                    <?php
                        for($l=0; $l<9; $l++)
                        {
                    ?>
                            <tr>
                                <td class="<?php echo $l==0? 'tableCorner': 'tableBorder'; ?>">
                                    <?php echo $l==0? "<br/>": chr(64+$l); ?>
                                </td>
                                <?php
                                    for($c=0; $c<8; $c++)
                                    {
                                        if($l>0)
                                        {
                                            ?>
                                                <td class="tableCell <?php echo ($c+$l)%2==0? 'dark':'light' ?>-cell">
                                                    &nbsp;
                                                </td>
                                            <?php
                                        }else{
                                            ?>
                                                <td class="tableBorderTop">
                                                    <?php echo $c+1;?>
                                                </td>
                                            <?php
                                        }
                                    }
                                ?>
                            </tr>
                    <?php
                        }
                    ?>
                    <!--<tr>
                        <td colspan="9" class="tableBorderTop">
                            Now playing:
                        </td>
                    </tr>-->
                </table>
            </div>
        </div>
    </body>
    <?php
        include('chessMate.php');
    ?>
</html>
