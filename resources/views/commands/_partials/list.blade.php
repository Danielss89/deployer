<div class="col-md-6">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-code"></i> {{ $step }} Commands</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-default" title="Add a new {{ strtolower($step) }} command" data-step="{{ $step }}" data-toggle="modal" data-target="#command"><i class="fa fa-plus"></i> Add Command</button>
            </div>
        </div>
        @if (!count($commands))
        <div class="box-body">
            <p>No commands have been configured</p>
        </div>
        @else
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Run As</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($commands as $command)
                    <tr id="command_{{ $command->id }}">
                        <td>{{ $command->name }}</td>
                        <td>{{ $command->user }}</td>
                        <td>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default" title="Edit the command" data-step="{{ $step }}" data-command-id="{{ $command->id }}" data-toggle="modal" data-target="#command"><i class="fa fa-edit"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>