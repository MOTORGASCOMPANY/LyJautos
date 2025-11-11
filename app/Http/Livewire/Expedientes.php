<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Expediente;
use App\Models\ExpedienteObservacion;
use App\Models\Imagen;
use App\Models\Observacion;
use App\Models\Taller;
use App\Models\TipoImagen;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Expedientes extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Filtros de búsqueda y ordenamiento
    public $search = "", $cant = "10", $sort = "created_at", $direction = 'desc', $es = '';
    // Expediente actual en edición
    public  $expediente, $identificador, $tallerSeleccionado, $servicioSeleccionado, $editando = false;
    // Datos relacionados
    public $observaciones = [], $talleres = [], $servicios = [];
      
    // Archivos
    public $fotosnuevas = []; // imagenes nuevas a subir    
    public $files = []; // imagenes ya guardadas en bd
    public $documentosnuevos = []; // documentos nuevos a subir
    public $documentos = []; // documentos ya guardados en bd
    
    public $tiposFotosNuevas = []; // relación [index => tipo_imagen_id]

    // Tipos de imagen
    public $tipos_imagen = []; // lista de tipos
    public $fileTipos = []; // almacena [imagen_id => tipo_imagen_id]
    
    // Control de carga diferida
    public $readyToLoad = false;

    public $comentario;
    /*public $idus, $ta;*/

    public $confidences = []; // índice => porcentaje de IA

    // Eventos de Livewire escuchados
    protected $listeners = ['render', 'delete', 'deleteFile'];

    // Parámetros de la URL
    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
        'search' => ['except' => ''],
        'es' => ['except' => ''],
    ];

    // Reglas de validación
    protected $rules = [
        'expediente.placa' => 'required|min:6|max:7',
        'expediente.certificado' => 'required|min:1|max:7',
        'fotosnuevas.*' => 'image|mimes:jpeg,png,jpg,gif,heic,heif|max:5120',
        'documentosnuevos.*' => 'mimes:pdf,xls,xlsx,doc,docx,txt|max:2048',
        'tallerSeleccionado' => 'required',
        'servicioSeleccionado' => 'required',
        // reglas nuevas para tipos
        'fileTipos' => 'array',
        'fileTipos.*' => 'nullable|integer|exists:tipos_imagen,id',
        'tiposFotosNuevas' => 'array',
        'tiposFotosNuevas.*' => 'nullable|integer|exists:tipos_imagen,id',
    ];

    // Se carga al inicializar el componente
    public function mount()
    {
        //$this->idus = Auth::id();
        $this->expediente = new Expediente();
        $this->identificador = rand();
        $this->talleres = Taller::all();
        $this->tipos_imagen = TipoImagen::all();
    }

    // Carga diferida de expedientes
    public function loadExpedientes()
    {
        $this->readyToLoad = true;
    }
    
    /** 
     * Envía rutas de imágenes al microservicio IA (FastAPI) y devuelve las clasificaciones resultantes.
     * $paths rutas absolutas de las imágenes locales (storage/app/...)
     * resultados devueltos por el servidor IA (class, confidence, path)
     */
    private function classifyImagesWithIA($paths)
    {
        try {
            $url = "http://127.0.0.1:8000/classify"; // Microservicio IA (FastAPI) local
            $response = Http::timeout(60)->post($url, ['paths' => $paths]);

            // Si la respuesta HTTP falla (por timeout, 500, etc.)
            if ($response->failed()) {
                Log::channel('ia_laravel')->error("[IA] Error HTTP al comunicarse con el servidor IA: " . $response->status());
                return [];
            }

            $data = $response->json();
            Log::channel('ia_laravel')->info("[IA] Respuesta recibida del servidor IA:", $data);

            // Devuelve la lista de resultados si existe
            return $data['results'] ?? [];
        } catch (\Throwable $e) {
            Log::channel('ia_laravel')->error("[IA] Excepción al consultar el servidor IA: " . $e->getMessage());
            return [];
        }
    }   
    /**
     * Detecta automáticamente el tipo de imagen cuando el usuario sube nuevas fotos.
     * 1. Guarda temporalmente las imágenes.
     * 2. Envía las rutas al servidor IA. (Http::post envía un POST request con el cuerpo JSON:)
     * 3. Procesa las respuestas y asigna tipo + nivel de confianza.
     */
    public function updatedFotosnuevas()
    {
        try {
            // Reiniciar arrays
            $this->tiposFotosNuevas = [];
            $this->confidences = [];

            // Guardar temporalmente las imágenes nuevas y construir rutas absolutas
            $paths = [];
            $tempPaths = [];

            foreach ($this->fotosnuevas as $key => $file) {
                $tempPath = $file->store('temp');
                $fullPath = storage_path('app/' . $tempPath);
                $paths[$key] = $fullPath;
                $tempPaths[$key] = $tempPath;
            }

            // Llamar al microservicio IA (FastAPI)
            $results = $this->classifyImagesWithIA(array_values($paths));

            // Procesar resultados
            foreach ($results as $i => $res) {
                $predictedClass = $res['class'] ?? null;
                $confidence = $res['confidence'] ?? 0;
                $path = $res['path'] ?? '';

                // Buscar el tipo de imagen según el código
                $tipo = null;
                if ($predictedClass) {
                    $tipo = TipoImagen::where('codigo', $predictedClass)->first();
                }

                // Buscar índice original por ruta
                $index = array_search($path, $paths);

                if ($index !== false) {
                    $this->tiposFotosNuevas[$index] = $tipo?->id;
                    $this->confidences[$index] = round($confidence, 2);

                    // Loguear detalle de predicción
                    Log::channel('ia_laravel')->info("[IA] Imagen clasificada", ['archivo' => $path, 'tipo' => $predictedClass, 'confianza' => $confidence, 'tipo_id' => $tipo?->id,]);
                }
            }

            // Limpiar archivos temporales
            foreach ($tempPaths as $tp) {
                Storage::delete($tp);
            }

            // Forzar actualización en Livewire
            $this->tiposFotosNuevas = array_values($this->tiposFotosNuevas);
            $this->confidences = array_values($this->confidences);
            $this->fotosnuevas = array_values($this->fotosnuevas);

            // Loguear resumen final del proceso
            Log::channel('ia_laravel')->info("[IA] Clasificación completada para nuevas fotos", ['tipos' => $this->tiposFotosNuevas, 'confidences' => $this->confidences]);

        } catch (\Throwable $e) {
            Log::channel('ia_laravel')->error("[IA] Error general al procesar nuevas fotos: " . $e->getMessage());
        }
    }

    // Ordenar columnas
    public function order($sort)
    {
        if ($this->sort === $sort) {
            $this->direction = $this->direction === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    // Cargar servicios de un taller
    public function listaServicios()
    {
        if ($this->tallerSeleccionado != null) {
            $this->servicios = json_decode(DB::table('servicio')
                ->select('servicio.*', 'tiposervicio.descripcion')
                ->join('tiposervicio', 'servicio.tipoServicio_idtipoServicio', '=', 'tiposervicio.id')
                ->where('taller_idtaller', $this->tallerSeleccionado)
                ->get(), true);
        }
    }

    // Cargar observaciones y comentario de un expediente
    public function cargaObservaciones(Expediente $expediente)
    {
        $nobs = ExpedienteObservacion::where('idExpediente', $expediente->id)->get();
        if ($nobs) {
            $this->reset(['observaciones']);
            foreach ($nobs as $n) {
                $ob = Observacion::find($n->idObservacion);
                if ($ob->tipo == 1)
                    array_push($this->observaciones, $ob);
                else {
                    $this->comentario = $ob->detalle;
                }
            }
        } else {
            $this->reset(['observaciones']);
        }
    }

    // Validar cada campo al actualizar
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    // Resetear página al cambiar búsqueda
    public function updatingSearch()
    {
        $this->resetPage();
    }
    // Limpiar arrays al iniciar edición
    public function updatingEditando()
    {
        $this->reset(['documentosnuevos', 'fotosnuevas']);
    }

    // Renderizar vista con expedientes
    public function render()
    {
        if ($this->readyToLoad) {
            $expedientes = Expediente::placaOcertificado($this->search)
                ->idInspector(Auth::id())
                ->estado($this->es)
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);
        } else {
            $expedientes = [];
        }
        return view('livewire.expedientes', compact('expedientes'));
    }

    // Abir modal y editar expediente
    public function edit(Expediente $expediente)
    {
        if ($expediente->id != null) {
            $this->expediente = $expediente;
            $this->files = Imagen::where('Expediente_idExpediente', '=', $expediente->id)->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp'])->get();
            $this->documentos = Imagen::where('Expediente_idExpediente', '=', $expediente->id)->whereIn('extension', ['pdf', 'xlsx', 'xls', 'docx', 'doc'])->get();
            $this->identificador = rand();
            $this->tallerSeleccionado = $expediente->idTaller;
            $this->listaServicios();
            $this->servicioSeleccionado = $expediente->servicio_idservicio;
            $this->cargaObservaciones($expediente);
            $this->editando = true;

            // inicializar el mapa fileTipos por id para los selects
            $this->fileTipos = [];
            
            foreach ($this->files as $file) {
                $this->fileTipos[$file->id] = $file->tipo_imagen_id; // puede ser null
                /*/ Generar URL temporal para las imágenes migradas a DigitalOcean (privadas)
                if ($file->migrado == 1) {
                    // Generamos la URL temporal solo si la imagen está migrada
                    // La URL solo será válida por 5 minutos y solo accesible por usuarios autenticados
                    $file->url_temporal = Storage::disk('do')->temporaryUrl($file->ruta, now()->addMinutes(5));
                } else {
                    // Si no está migrada, dejamos la URL pública en Storage local
                    $file->url_temporal = Storage::url($file->ruta);
                }*/
            }
        }
    }
    // Guardar cambios en expediente
    public function actualizar(){
        $this->validate();

        // Guardar fotos nuevas
        /*if(count($this->fotosnuevas)>0){
            foreach($this->fotosnuevas as $key=>$fn){
                $file_sa= new imagen();
                $file_sa->nombre=trim($this->expediente->placa).'-foto'.($key+1).$this->identificador.'-'.$this->expediente->certificado;
                $file_sa->extension=$fn->extension();
                $file_sa->ruta = $fn->storeAs('public/expedientes',$file_sa->nombre.'.'.$fn->extension());
                $file_sa->Expediente_idExpediente=$this->expediente->id;

                // Crear el registro e incluir el tipo seleccionado (si existe)
                Imagen::create([
                    'nombre'=>$file_sa->nombre,
                    'ruta'=>$file_sa->ruta,
                    'extension'=>$file_sa->extension,
                    'Expediente_idExpediente'=>$file_sa->Expediente_idExpediente,
                    'tipo_imagen_id' => $this->tiposFotosNuevas[$key] ?? null,
                ]);
            }
        }*/
        foreach ($this->fotosnuevas as $idx => $tempFile) {
            $nombreBase = trim($this->expediente->placa) . '-foto' . $idx . '-' . $this->identificador . '-' . $this->expediente->certificado;
            $ext = $tempFile->getClientOriginalExtension();
            $ruta = $tempFile->storeAs('public/expedientes', $nombreBase . '.' . $ext);

            Imagen::create([
                'nombre' => $nombreBase,
                'ruta' => $ruta,
                'extension' => $ext,
                'Expediente_idExpediente' => $this->expediente->id,
                'tipo_imagen_id' => $this->tiposFotosNuevas[$idx] ?? null,

                'confidence' => $this->confidences[$idx] ?? 0,
                'clasificado_por_ia' => $this->tiposFotosNuevas[$idx] ? 1 : 0,
            ]);
        }

        // Guardar documentos nuevos
        /*foreach($this->documentosnuevos as $key=>$file){
            $file_save= new imagen();
            $file_save->nombre=trim($this->expediente->placa).'-doc'.($key+1).$this->identificador.'-'.$this->expediente->certificado;
            $file_save->extension=$file->extension();
            $file_save->ruta = $file->storeAs('public/expedientes',$file_save->nombre.'.'.$file->extension());
            $file_save->Expediente_idExpediente=$this->expediente->id;

            Imagen::create([
                'nombre'=>$file_save->nombre,
                'ruta'=>$file_save->ruta,
                'extension'=>$file_save->extension,
                'Expediente_idExpediente'=>$file_save->Expediente_idExpediente,
            ]);
        }*/
        foreach ($this->documentosnuevos as $key => $file) {
            $nombreBase = trim($this->expediente->placa).'-doc'.($key+1).$this->identificador.'-'.$this->expediente->certificado;
            $ext = $file->extension();
            $ruta = $file->storeAs('public/expedientes',$nombreBase.'.'.$ext);

            Imagen::create([
                'nombre'=>$nombreBase,
                'ruta'=>$ruta,
                'extension'=>$ext,
                'Expediente_idExpediente'=>$this->expediente->id,
            ]);
        }

        // Actualizar expediente
        $this->expediente->idTaller = $this->tallerSeleccionado;
        $this->expediente->estado = 1;
        $this->expediente->servicio_idservicio = $this->servicioSeleccionado;
        $this->expediente->save();

        // Actualizar tipos de las fotos ya existentes usando fileTipos (id => tipo_id)
        foreach ($this->fileTipos as $imagenId => $tipoId) {
            if ($img = Imagen::find($imagenId)) {
                $img->tipo_imagen_id = $tipoId ? (int)$tipoId : null;
                $img->save();
            }
        }

        $this->reset(['expediente', 'fotosnuevas', 'tiposFotosNuevas', 'documentosnuevos', 'fileTipos']);
        $this->emit('alert','El expediente se actualizo correctamente');
        $this->identificador = rand();
        $this->editando = false;
    }

    public function delete(Expediente $expediente)
    {
        $imgs = Imagen::where('Expediente_idExpediente', '=', $expediente->id)->get();
        foreach ($imgs as $img) {
            if($img->migrado==0){
                Storage::delete($img->ruta);
            }else{
                Storage::disk('do')->delete($img->ruta);
            }
        }
        $expediente->delete();
        $this->expediente = Expediente::make();
        $this->emitTo('expedientes', 'render');
        $this->identificador = rand();
        $this->resetPage();
    }

    // borrar imagen de bd
    public function deleteFile(Imagen $file)
    {
        //Storage::delete([$file->ruta]);
        if($file->migrado==0){
            Storage::delete($file->ruta);
        }else{
            Storage::disk('do')->delete($file->ruta);
        }
        $file->delete();
        $this->reset(["files"]);
        // $this->identificador=rand();
        $this->files = Imagen::where('Expediente_idExpediente', '=', $this->expediente->id)->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp'])->get();
    }
    // borrar documentos en bs
    public function deleteDocument(Imagen $file)
    {
        if($file->migrado==0){
            Storage::delete($file->ruta);
        }else{
            Storage::disk('do')->delete($file->ruta);
        }
        $file->delete();
        $this->reset(["documentos"]);
        // $this->identificador=rand();
        $this->documentos = Imagen::where('Expediente_idExpediente', '=', $this->expediente->id)->whereIn('extension', ['pdf', 'xlsx', 'xls', 'docx', 'doc'])->get();
    }

    /*public function deleteFileUpload($id)
    {
        unset($this->fotosnuevas[$id]);
    }*/
    // eliminar foto nueva antes de guardar
    public function deleteFileUpload($id)
    {
        if (isset($this->fotosnuevas[$id])) {
            unset($this->fotosnuevas[$id]);
        }
        if (isset($this->tiposFotosNuevas[$id])) {
            unset($this->tiposFotosNuevas[$id]);
        }
    }
    // eliminar documento nueva antes de guardar
    public function deleteDocumentUpload($id)
    {
        unset($this->documentosnuevos[$id]);
    }
    
}


// Clasifica una imagen usando el script Python, devuelve [tipo_imagen_id, confidence] o [null, 0] en caso de error
/*private function classifyImageWithAI($file)
    {
        try {
            // Rutas absolutas
            $python = '/var/www/html/mtg/scripts/venv/bin/python3';
            $script = '/var/www/html/mtg/scripts/clasificar.py';

            // Guardar temporalmente
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);

            // Preparar comando y registrar ejecución
            $command = sprintf("%s %s '%s' 2>&1", $python, $script, addslashes($fullPath));
            // Log para monitorear el comando
            Log::info("[IA] Ejecutando comando: {$command}");

            // Ejecutar el script y capturar salida (stdout + stderr)
            $output = shell_exec($command);
            Log::info("[IA] Salida completa del script:\n" . $output);

            // Validar que haya respuesta
            if (empty(trim($output))) {
                Log::error("[IA] Salida vacía para {$file->getClientOriginalName()}");
                return [null, 0];
            }

            // Tomar la última línea no vacía (donde está la predicción real)
            $lines = array_filter(array_map('trim', explode("\n", $output)));
            $line = end($lines);
            Log::info("[IA] Línea final analizada: {$line}");


            // Verificar si el script devolvió un error
            if (str_starts_with(strtolower($line), 'error')) {
                Log::warning("[IA] Script devolvió error: {$line}");
                return [null, 0];
            }

            // Validar formato esperado "clase|confianza"
            if (!str_contains($line, '|')) {
                Log::warning("[IA] Salida inesperada (sin '|'): {$line}");
                return [null, 0];
            }

            // Extraer valores
            [$predictedClass, $confidence] = explode('|', $line);
            $predictedClass = trim($predictedClass);
            $confidence = round(floatval($confidence), 2);
            Log::info("[IA] PredictedClass = {$predictedClass} | Confianza = {$confidence}%");

            // Buscar tipo en BD
            $tipo = TipoImagen::where('codigo', $predictedClass)->first();

            if ($tipo) {
                Log::info("[IA] TipoImagen encontrado: ID={$tipo->id}, Código={$tipo->codigo}");
            } else {
                Log::warning("[IA] No se encontró TipoImagen con código '{$predictedClass}'");
                $existentes = TipoImagen::pluck('codigo')->implode(', ');
                Log::info("[IA] Códigos existentes: {$existentes}");
            }

            // Eliminar archivo temporal
            Storage::delete($tempPath);

            return [$tipo->id ?? null, $confidence];
        } catch (\Throwable $e) {
            Log::error("Excepción al clasificar imagen {$file->getClientOriginalName()}: " . $e->getMessage());
            return [null, 0];
        }
    }
*/

// llama a classifyImagesWithIA de clasificar.py
/*public function updatedFotosnuevas()
    {
        foreach ($this->fotosnuevas as $key => $file) {
            // Reinicia el tipo y confianza previos
            $this->tiposFotosNuevas[$key] = null;
            $this->confidences[$key] = 0;

            // Clasificar imagen con el modelo IA
            [$tipoId, $confidence] = $this->classifyImageWithAI($file);

            // Si la IA devuelve un tipo válido, lo asigna
            if ($tipoId) {
                $this->tiposFotosNuevas[$key] = $tipoId; // asigana valor al select
            }

            // Guarda la confianza (aunque sea 0) para mostrarla en la vista
            $this->confidences[$key] = $confidence ?? 0;
        }

        // fuerza que Livewire detecte los cambios en el arrays
        $this->tiposFotosNuevas = array_values($this->tiposFotosNuevas);
        $this->fotosnuevas = array_values($this->fotosnuevas);
        $this->confidences = array_values($this->confidences);
    }
*/