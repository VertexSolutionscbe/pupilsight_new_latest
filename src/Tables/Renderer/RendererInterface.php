<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Tables\Renderer;

use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;

interface RendererInterface
{
    public function renderTable(DataTable $table, DataSet $dataSet);
}
