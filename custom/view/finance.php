<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap-3.3.5-dist/css/bootstrap.min.css">
    <script src="../vendor/jquery/dist/jquery.min.js"></script>
    <script>
        function addCard(){
            var card_id = $("#cards input").length + 1;

            $("#cards").append("<br>Card"+card_id+"：<input style='margin-top: 5px;' class='form-control' " +
                "type='text' name='card["+card_id+"]' id='card["+card_id+"]'>");
        }
        $(document).ready(function(){
            if($("#transaction_type").val() == 'buy_new'){
                $(".new_card_panel").show();
                $("#purchasing_panel").hide();
            }else{
                $(".new_card_panel").hide();
                $("#purchasing_panel").show();
            }

            $("#transaction_type").change(function(){
                if($("#transaction_type").val() == 'buy_new'){
                    $(".new_card_panel").show();
                    $("#purchasing_panel").hide();
                }else{
                    $(".new_card_panel").hide();
                    $("#purchasing_panel").show();
                }
            });
        });
    </script>
</head>
<body>
<div class="container">
    <div id="left-container" style="display:inline-block;vertical-align:top;width:49%">
        <h2>Update Raise order information</h2>
        <div name="form">
            <form action="../controller/finance.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Please choose a file</label>
                    <input type="file" id="file" name="file">
                    <input type="hidden" id="raise_order" name="type" value="raise_order">
                    <p class="help-block">Please select raise order file（*.csv）.</p>
                </div>
                <button type="submit" class="btn btn-default">Confirm</button>
            </form>
        </div>

        <h2>Update Raise account information</h2>
        <div name="form">
            <form action="../controller/finance.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Please choose a file</label>
                    <input type="file" id="file" name="file">
                    <input type="hidden" id="raise_account" name="type" value="raise_account">
                    <p class="help-block">Please select raise account file（*.csv）.</p>
                </div>
                <button type="submit" class="btn btn-default">Confirm</button>
            </form>
        </div>
    </div>
    <div id="right-container" style="display:inline-block;vertical-align:top;width:49%;">
        <h2>Gift card transactions</h2>
        <form class="form-inline" action="../controller/transaction.php">
            <div id="cards">
                Card1：<input class="form-control" type="text" name="card[1]" id="card[1]">
                <div onclick="addCard()" class='btn btn-default'>Add new card</div>
            </div>
            <table>
                <tr>
                    <td style="width: 12%">
                        <label for="transaction_type">Transaction type</label>
                    </td>
                    <td style="width: 28%">
                        <select class="form-control" style="margin-top: 10px;" id="transaction_type" name="transaction_type">
                            <option value="buy_new">Buy a new walmart card</option>
                            <option value="purchasing">Purchasing</option>
                        </select>
                    </td>
                </tr>
                <tr class="new_card_panel">
                    <td>
                        <label for="new_card">New Card Account</label>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="new_card" id="new_card">
                    </td>
                </tr>
                <tr class="new_card_panel">
                    <td>
                        <label for="new_card">New Card Pin</label>
                    </td>
                    <td>
                        <input class="form-control" type="number" name="pin" id="pin">
                    </td>
                </tr>

                <tr id="purchasing_panel">
                    <td>
                        <label for="purchasing">Purchasing Note</label>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="note" id="purchasing">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="amount">Amount</label>
                    </td>
                    <td>
                        <input class="form-control" type="number" name="amount" id="amount">
                    </td>
                </tr>
            </table>
            <div style="margin-top: 10px;">
                <input type="submit" class="btn btn-default">
            </div>
        </form>

    </div>


</div>
</body>
</html>