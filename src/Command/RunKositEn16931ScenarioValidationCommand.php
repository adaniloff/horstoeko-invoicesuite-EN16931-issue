<?php

declare(strict_types=1);

namespace App\Command;

use App\FacturX\DemoInvoiceFactory;
use horstoeko\invoicesuite\validators\InvoiceSuiteKositDocumentValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Step 3.
 */
#[AsCommand(
    name: 'app:factur-x:kosit-en16931',
    description: 'Valide la facture Factur-X avec le scénario EN16931 (CII) isolé (artefacts CEF 1.3.16)',
)]
final class RunKositEn16931ScenarioValidationCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('broken', null, InputOption::VALUE_NONE, 'Utilise la variante à total TTC incohérent (step 4)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $broken = (bool) $input->getOption('broken');

        $projectDir = dirname(__DIR__, 2);
        $scenarioSourceDir = $projectDir.'/kosit/en16931-scenario';
        $workDir = $projectDir.'/var/kosit-en16931'.($broken ? '-broken' : '');
        @mkdir($workDir, 0777, true);

        $scenarioZipPath = $workDir.'/en16931-scenario.zip';
        $this->zipDirectory($scenarioSourceDir, $scenarioZipPath);

        $builder = DemoInvoiceFactory::build($broken);

        $io->section(sprintf('Validation KoSIT (scénario EN16931 isolé, variante %s)', $broken ? 'cassée' : 'valide'));
        $io->text(sprintf('Répertoire de travail : %s', $workDir));
        $io->text(sprintf('Paquet de scénario : %s', $scenarioZipPath));

        $validator = InvoiceSuiteKositDocumentValidator::createFromDocumentBuilder($builder)
            ->setBaseDirectory($workDir)
            ->setValidatorScenarioDownloadUrl('file://'.$scenarioZipPath)
            ->setValidatorScenarioZipFilename('en16931-scenario.zip')
            ->setValidatorAppScenarioFilename('scenarios.xml')
            ->disableCleanup()
            ->validate();

        $io->section('Messages INFO (sortie du processus Java)');
        foreach ($validator->getInfoMessagesInMessageBag() as $message) {
            $io->writeln(' - '.$message->getMessageContent());
        }

        $io->section('Messages WARNING');
        foreach ($validator->getWarningMessagesInMessageBag() as $message) {
            $io->writeln(' - '.$message->getMessageContent());
        }

        $io->section('Messages ERROR');
        foreach ($validator->getErrorMessagesInMessageBag() as $message) {
            $io->writeln(' - '.$message->getMessageContent());
        }

        $io->section('Messages INTERNALERROR');
        foreach ($validator->getInternalErrorMessagesInMessageBag() as $message) {
            $io->writeln(' - '.$message->getMessageContent());
        }

        $io->section('Récapitulatif');
        $io->table(
            ['Sévérité', 'Nombre'],
            [
                ['INFO', $validator->countInfoMessagesInMessageBag()],
                ['WARNING', $validator->countWarningMessagesInMessageBag()],
                ['ERROR', $validator->countErrorMessagesInMessageBag()],
                ['INTERNALERROR', $validator->countInternalErrorMessagesInMessageBag()],
            ]
        );

        if ($validator->hasInternalErrorMessagesInMessageBag()) {
            $io->error('INTERNALERROR - la chaîne technique a échoué, voir les messages ci-dessus');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function zipDirectory(string $sourceDir, string $zipPath): void
    {
        if (is_file($zipPath)) {
            unlink($zipPath);
        }

        $zip = new \ZipArchive();

        if (true !== $zip->open($zipPath, \ZipArchive::CREATE)) {
            throw new \RuntimeException(sprintf('Impossible de créer l\'archive %s', $zipPath));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            $localName = substr($file->getPathname(), strlen($sourceDir) + 1);
            $zip->addFile($file->getPathname(), $localName);
        }

        $zip->close();
    }
}
