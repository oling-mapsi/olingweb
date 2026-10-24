<?php
namespace App\Service\Ai;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
final class AiArtifacts
{
    private string $root;
    public function __construct(#[Autowire('%kernel.project_dir%')] string $projectDir) {$this->root=$projectDir.'/var/ai-private';}
    public function put(string $content): array
    {
        if(strlen($content)>1048576)throw new \InvalidArgumentException('Résultat trop volumineux.');
        if(!is_dir($this->root)&&!mkdir($this->root,0700,true)&&!is_dir($this->root))throw new \RuntimeException('Stockage indisponible.');
        $hash=hash('sha256',$content);$key=$hash.'.txt';$path=$this->root.'/'.$key;
        if(is_link($path))throw new \RuntimeException('Chemin refusé.');
        if(file_put_contents($path,$content,LOCK_EX)===false)throw new \RuntimeException('Écriture impossible.');
        chmod($path,0600);return ['storage_key'=>$key,'sha256'=>$hash,'bytes'=>strlen($content),'mime'=>'text/plain'];
    }
    public function path(array $asset): string
    {
        if(!preg_match('/^[a-f0-9]{64}\.txt$/D',$asset['storage_key']))throw new \RuntimeException('Chemin refusé.');
        $path=$this->root.'/'.$asset['storage_key'];$real=realpath($path);
        if(!$real||is_link($path)||dirname($real)!==realpath($this->root)||!hash_equals($asset['sha256'],hash_file('sha256',$real)))throw new \RuntimeException('Preuve absente ou altérée.');
        return $real;
    }
}
