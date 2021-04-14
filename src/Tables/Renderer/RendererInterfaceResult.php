<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Renderer;

use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTableResult;

interface RendererInterfaceResult
{
    public function renderTable(DataTableResult $table, DataSet $dataSet);
}
