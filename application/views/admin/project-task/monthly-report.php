<style>
    table{
        font-size:10px;
    }
</style>
<div class="container" style="width:95%">
    <h3>Monthly Report: <?= app\components\Helpers::i()->formatDate($first_date,"M Y") ?></h3>
<hr />

 

 
 
<table class="table table-bordered table-sm">
    <thead>
        
    </thead>
    <tbody>
        <?php 
        $gtotal = 0;
        foreach(["Hard","Soft"] as $type2){ ?>
        <tr>
            <th colspan="12">
                <?= $type2 ?>
            </th>
        </tr>
        <tr>
            <th>S.No.</th>
            <th>TaskID</th>
            <th>Project</th>            
            <th>Work Type</th>
            <th>Task Nature</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>Amount</th>
            <!-- <th>Estimated Date</th> -->
            <th>Completion Date</th>
            <!-- <th>Status</th> -->
            <th>Alotted By</th>
            <th>Alotted To</th>
            <th>Operator</th>
        </tr>
        <?php 
        $total = 0; $n=0;
        if(!isset($projects[$type2])){
            continue;
        }
        foreach($projects[$type2] as $id=>$projModel){
         
        foreach($tasks[$id]  as $m){ 
            $total += $m->ts_amount;
            $n++;
            ?>
        <tr>
            <td><?= $n ?></td>
            <td><?= $m->ts_id ?></td>
            <td><?= $m->cost->project->proj_title ?></td>
            <td><?= $m->cost->cost_title ?></td>
            <td><?= $m->task->task_title ?></td>
            <td><?= $m->ts_qty ?></td>
            <td><?= $m->ts_rate ?></td>
            <td><?= $m->ts_amount ?></td>
            <!-- <td><?= app\components\Helpers::i()->formatDate($m->ts_estimated_date,"d M Y") ?></td> -->
            <td>
                <?php if($m->ts_status == "Completed"){ ?>
                <?= app\components\Helpers::i()->formatDate($m->ts_completion_date,"d M Y") ?>
                <?php } ?>
            </td>
            <!-- <td>
                <?php if($m->ts_status != "Completed"){ ?>
                <strong><?= $m->ts_status ?></strong>
                <?php } else { ?>
                <?= $m->ts_status ?>
                <?php } ?>
            </td> -->
            <td><?= !is_null($m->alottedBy) ? $m->alottedBy->emp_fullname : "" ?></td>
            <td><?= !is_null($m->alottedTo) ? $m->alottedTo->emp_fullname : "" ?></td>
            <td><?= $m->user->user_name ?></td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr>
            <th colspan="7"><strong>Total: </strong></th>
            <th><?= $total ?></th>
            <th colspan="4"></th>
        </tr>
        
        <?php $gtotal += $total;         
        } ?>
         
    </tbody>
    <tfoot>
        <tr>
            <th colspan="7"><strong>Grand Total: </strong></th>
            <th><?= $gtotal ?></th>
            <th colspan="4"></th>
        </tr>
    </tfoot>
    
</table>



  
</div>

<script>
    window.print();
    </script>
    <div class="text-center hidden-print">
        <button onclick="window.print()" class="btn btn-primary">
            Print
        </button>
    </div>