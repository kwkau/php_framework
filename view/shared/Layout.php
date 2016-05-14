
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8"/>
            <meta name="description" content="test app"/>
            <title><?= $this->viewBag['title']?></title>
            <!-- page styles -->
            <?=$this->htmlLink('bootstrap.css','stylesheet')?>
            <?=$this->htmlLink('shared.css','stylesheet')?>
            <?=$this->htmlScript('jquery-1.10.2.min.js','text/javascript')?>
            <?=$this->htmlScript('sswap-lib.js')?>
        </head>
        <body>
            <!--page header-->
            <div id=header-cont class="row">
                    <?=$this->shared('header');?>
            </div>


            <!--main content-->
            <div class="row">
                    <!--content-->
                    <?
                        $this->layout_body($this->page);
                    ?>
            </div>



            <!-- page scripts -->
            <?=$this->htmlScript('app/app_services.js')?>
            <?=$this->htmlScript('bootstrap.min.js')?>
        </body>
    </html>