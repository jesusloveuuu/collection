<?php

namespace App\Imports;

use App\Models\Tag;
use App\Models\Term;
use App\Models\TermsTagsPivot;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TermsTagsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        echo"Row\n";
        var_dump($row);

        if(!empty($row['term']) && !empty($row['tag'])){
            $str_term = $row['term'];
            $str_tag = $row['tag'];

            //关联
            if(1){
                //查找tag，不存在则创建
                $tag = Tag::where('tag', '=', $str_tag)->orderBy('tag', 'desc')->first();
                if (empty($tag)) {
                    echo "Creating... Tag: $str_tag \n";
                    $tag = new Tag();
                    $tag->tag = $str_tag;
                    $tag->save();
                } else {
                    echo "Already exists, Tag: $tag->tag \n";
                }


                //查找term：不存在则创建
                $term = Term::where('term', '=', $str_term)->orderBy('term', 'desc')->first();
                if (empty($term)) {
                    echo "Creating...Term: $str_term \n";
                    $term = new Term();
                    $term->term = $str_term;
                    $term->classification = $str_tag;
                    $term->type = Term::TYPE_TERM;
                    $term->save();
                } else {
                    echo "Already exists, Term: $term->term \n";
                }
            }


            //排重
            $t_t_p = TermsTagsPivot::where('term',$row['term'])->where('tag',$row['tag'])->first();
            if(empty($t_t_p)){
                echo "Creating...TermsTags \n";
                return new TermsTagsPivot([
                    'term' => $row['term'],
                    'tag' => $row['tag'],
                ]);
            }else{
                echo "Already exists, TermsTags: $t_t_p->id \n";
            }
        }
    }
}
