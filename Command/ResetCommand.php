<?php

namespace FOS\ElasticaBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Resetter;

/**
 * Reset search indexes.
 */
class ResetCommand extends Command
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var Resetter
     */
    private $resetter;

    public function __construct(IndexManager $indexManager, Resetter $resetter)
    {
        parent::__construct();

        $this->indexManager = $indexManager;
        $this->resetter = $resetter;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('fos:elastica:reset')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to reset')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to reset')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force index deletion if same name as alias')
            ->setDescription('Reset search indexes')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $input->getOption('index');
        $type = $input->getOption('type');
        $force = (bool) $input->getOption('force');

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $type) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        } else {
            $indexes = null === $index
                ? array_keys($this->indexManager->getAllIndexes())
                : array($index)
            ;

            foreach ($indexes as $index) {
                $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
                $this->resetter->resetIndex($index, false, $force);
            }
        }
    }
}
