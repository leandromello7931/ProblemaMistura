<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mistura;
use App\Ingrediente;
use App\Nutriente;
use App\Restricao;
use App\ItemMistura;
use DB;

class MisturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list_mistura = Mistura::paginate(15);
        return view('mistura.index', [
            'misturas' => $list_mistura
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $ultimaMistura = Mistura::count();
        return view('mistura.add_mistura')->with('ultimaMistura', $ultimaMistura+1);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'ingredientes' => 'required',
            'nutrientes' => 'required',
            'restricoes' => 'required'
        ]);

        // insert into ingredientes table
        foreach(explode(',', $request->ingredientes) as $ingredientes){
            $ingrediente_db = DB::table('ingredientes')
            ->where ('nome', '=', $ingredientes)
            ->select('id', 'nome', 'custo')
            ->get();

            if (count($ingrediente_db) > 0){
                $idIngredientes[] = $ingrediente_db->values()->get(0);
            }else{
                $idIngredientes[] = Ingrediente::firstOrCreate([
                    'nome' => $ingredientes,
                    'custo' => null
                ]);
            }
            
        }

        //insert into table nutrientes
        foreach(explode(',', $request->nutrientes) as $nutrientes){
            $nutriente_db = DB::table('nutrientes')
            ->where ('nome', '=', $nutrientes)
            ->select('id', 'nome')
            ->get();

            if (count($nutriente_db) > 0){
                $idNutrientes[] = $nutriente_db->values()->get(0);
            }else{
                $idNutrientes[] = Nutriente::firstOrCreate(
                    ['nome' => $nutrientes]
                );
            }
        }

        //insert into table restricoes
        foreach(explode(',', $request->restricoes) as $restricoes){
            $restricoes_db = DB::table('restricoes')
            ->where ('nome', '=', $restricoes)
            ->select('id', 'nome')
            ->get();

            if (count($restricoes_db) > 0){
                $idRestricoes[] = $restricoes_db->values()->get(0);
            }else{
                $idRestricoes[] = Restricao::firstOrCreate(
                    ['nome' => $restricoes]
                );
            }
        }

        //insert into table mistura
        $idMistura = DB::table('misturas')->insertGetId(
            ['nome' => $request->nomeMistura]
        );
        
        //insert into item mistura
        foreach( $idIngredientes as $ingrediente_item){
            foreach($idNutrientes as $nutriente_item){
                DB::table('itens_mistura')->insert([
                    'id_mistura' => $idMistura, 
                    'id_ingrediente' => $ingrediente_item->id, 
                    'id_nutriente' => $nutriente_item->id, 
                    'valor_ingredxnutr' => null
                ]);
            }
        }

        //insert into misturaRestricao
        foreach($idNutrientes as $nutriente_item){
            foreach($idRestricoes as $restricao_item){
                DB::table('misturas_restricoes')->insert([
                    'id_mistura' => $idMistura,
                    'id_nutriente' => $nutriente_item->id,
                    'id_restricao' => $restricao_item->id,
                    'valor_restricao' => null
                ]);
            }
        }
        return redirect()->action('ItemMisturaController@edit', ['id' => $idMistura]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $mistura = Mistura::find($id);
        return view('mistura.add_itens_mistura')->with('mistura', $mistura);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $mistura = Mistura::find($id);
        $mistura->delete();
        return redirect()->action('MisturaController@index')->with('success', 'Nutriente excluído com sucesso');
    
    }
}
