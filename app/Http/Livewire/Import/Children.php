<?php

namespace App\Http\Livewire\Import;

use App\Actions\Import\SingleChildImportAction;
use App\Http\Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;

class Children extends Component
{
    use WithFileUploads;

    public $organization_id;
    public $excelFile;

    protected $listeners = ['updateValueByKey'];

    private $child;

    public function import()
    {
        $mimes = implode(',', [
            'xls',
            'xlsx',
            'ods',
            'csv',
        ]);

        $this->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'excelFile' => ['required', 'file', 'mimes:'.$mimes],
        ]);

        SimpleExcelReader::create($this->excelFile->getRealPath())
            ->headersToSnakeCase()
            ->getRows()
            ->each(function (array $userData) {
                $this->child = SingleChildImportAction::run($this->child, $this->organization_id, $userData, false);
            });

        $this->excelFile = null;

        $this->emitMessage('success', trans('import.success_msg'));
    }

    public function render()
    {
        return view('livewire.import.children');
    }
}
