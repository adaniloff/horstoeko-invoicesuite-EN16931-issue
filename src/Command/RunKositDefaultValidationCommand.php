<?php

declare(strict_types=1);

namespace App\Command;

use App\FacturX\DemoInvoiceFactory;
use horstoeko\invoicesuite\validators\InvoiceSuiteKositDocumentValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Step 2.
 */
#[AsCommand(
    name: 'app:factur-x:kosit-default',
    description: 'Valide la facture Factur-X avec KoSIT en configuration par défaut (scenario XRechnung)',
)]
final class RunKositDefaultValidationCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $baseDirectory = sprintf('%s/var/kosit-default', dirname(__DIR__, 2));
        @mkdir($baseDirectory, 0777, true);

        $builder = DemoInvoiceFactory::build(false);

        $io->section('Validation KoSIT (configuration par défaut, scénario XRechnung)');
        $io->text(sprintf('Répertoire de travail demandé : %s', $baseDirectory));

        $validator = InvoiceSuiteKositDocumentValidator::createFromDocumentBuilder($builder)
            ->setBaseDirectory($baseDirectory)
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

        return Command::SUCCESS;
    }
}
