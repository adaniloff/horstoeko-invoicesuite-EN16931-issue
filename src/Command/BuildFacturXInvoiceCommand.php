<?php

declare(strict_types=1);

namespace App\Command;

use App\FacturX\DemoInvoiceFactory;
use horstoeko\invoicesuite\validators\InvoiceSuiteXsdDocumentValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:factur-x:build',
    description: 'Construit une facture Factur-X (profil EN 16931 / zffxcomfort) et la valide au XSD',
)]
final class BuildFacturXInvoiceCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('broken', null, InputOption::VALUE_NONE, 'Génère la variante avec total TTC incohérent (BR-CO-15)')
            ->addOption('out', null, InputOption::VALUE_REQUIRED, 'Chemin de sortie du XML', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $broken = (bool) $input->getOption('broken');

        $outFile = $input->getOption('out') ?? sprintf(
            '%s/var/factur-x/%s.xml',
            dirname(__DIR__, 2),
            $broken ? 'invoice-broken' : 'invoice-valid'
        );

        @mkdir(dirname($outFile), 0777, true);

        $builder = DemoInvoiceFactory::build($broken);
        $builder->saveContentToFile($outFile);

        $io->section(sprintf('Facture générée (%s)', $broken ? 'variante cassée' : 'variante valide'));
        $io->text($outFile);

        $validator = InvoiceSuiteXsdDocumentValidator::createFromDocumentBuilder($builder)->validate();

        $hasInternalError = $validator->hasInternalErrorMessagesInMessageBag();
        $hasError = $validator->hasErrorMessagesInMessageBag();

        $io->section('Validation XSD');

        if ($hasInternalError) {
            $io->error('INTERNALERROR - le validateur ne sait pas si le document est valide');
            foreach ($validator->getInternalErrorMessagesInMessageBag() as $message) {
                $io->writeln(' - '.$message->getMessageContent());
            }

            return Command::FAILURE;
        }

        if ($hasError) {
            $io->warning('ERROR - le document viole le schéma XSD');
            foreach ($validator->getErrorMessagesInMessageBag() as $message) {
                $io->writeln(' - '.$message->getMessageContent());
            }

            return Command::FAILURE;
        }

        $io->success('Validation XSD : OK (aucun ERROR, aucun INTERNALERROR)');

        return Command::SUCCESS;
    }
}
