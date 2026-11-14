<?php

namespace CryptoNoises\Commands;

use CryptoNoises\Bots\Threads\DiscordAssistantThread;
use CryptoNoises\Bots\Threads\DiscordCommandsThread;
use CryptoNoises\Bots\Threads\DiscordMainLoopThread;
use CryptoNoises\Bots\Threads\DiscordNotifierThread;
use CryptoNoises\Config;
use CryptoNoises\Core\Log;
use CryptoCore\Process\ProcessList;
use VssCfg;

class DiscordBot extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct();

        Config::cfg()->initializeByPlatform(Config::kPLATFORM_Discord);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function startMainLoop()
    {
        set_time_limit(0);

        if ($this->isThreadActive('main-loop'))
        {
            if ($this->verbose)
                $this->logMsg("Thread [main-loop] already active");

            return;
        }

        if (!$this->getCmdVar('silent'))
            Log::NoticeAndAlert(get_class($this), __FUNCTION__, "Start bot");

        $dcBotThread = new DiscordMainLoopThread(kDISCORD_TOKEN_MAINBOT);
        $dcBotThread->logVerbose = $this->verbose;
        $dcBotThread->run();
    }

    public function startNotifier()
    {
        set_time_limit(0);

        if ($this->isThreadActive('notifier'))
        {
            if ($this->verbose)
                $this->logMsg("Thread [notifier] already active");

            return;
        }

        if (!$this->getCmdVar('silent'))
            Log::NoticeAndAlert(get_class($this), __FUNCTION__, "Start bot");

        $dcBotThread = new DiscordNotifierThread(kDISCORD_TOKEN_MAINBOT);
        $dcBotThread->logVerbose = $this->verbose;
        $dcBotThread->run();
    }

    public function startAssistant()
    {
        set_time_limit(0);

        if ($this->isThreadActive('assistant'))
        {
            if ($this->verbose)
                $this->logMsg("Thread [assistant] already active");

            return;
        }

        if (!$this->getCmdVar('silent'))
            Log::NoticeAndAlert(get_class($this), __FUNCTION__, "Start bot");

        $dcBotThread = new DiscordAssistantThread(kDISCORD_TOKEN_ASSISTANT);
        $dcBotThread->logVerbose = $this->verbose;
        $dcBotThread->run();
    }

    //------------------------------------------------------------------------------------------------------------------

    public function stopAll()
    {
        $this->stopMainLoop();
        $this->stopNotifier();
        $this->stopAssistant();
    }

    public function stopMainLoop()
    {
        $this->verbose = ! $this->getCmdVar('silent', 0);
        $this->stopThread('main-loop');
    }

    public function stopNotifier()
    {
        $this->verbose = ! $this->getCmdVar('silent', 0);
        $this->stopThread('notifier');
    }

    public function stopAssistant()
    {
        $this->verbose = ! $this->getCmdVar('silent', 0);
        $this->stopThread('assistant');
    }

    //------------------------------------------------------------------------------------------------------------------

    public function commandsShow()
    {
        $dcBotThread = new DiscordCommandsThread(kDISCORD_TOKEN_MAINBOT);
        $dcBotThread->show();
    }

    public function commandsCreate()
    {
        $commands = $this->getCmdVar('only-commands');

        $dcBotThread = new DiscordCommandsThread(kDISCORD_TOKEN_MAINBOT);
        $dcBotThread->create($commands);
    }

    public function commandsDelete()
    {
        $commands = $this->getCmdVar('only-commands');

        $dcBotThread = new DiscordCommandsThread(kDISCORD_TOKEN_MAINBOT);
        $dcBotThread->delete($commands);
    }

    //------------------------------------------------------------------------------------------------------------------

    private function isThreadActive(string $threadScope): bool
    {
        $activeThreads = count($this->findThreadProcesses($threadScope));

        if ($activeThreads > 1)
            Log::Warning(get_class($this), __FUNCTION__, "Too many active threads($activeThreads) for [$threadScope]");

        return $activeThreads > 0;
    }

    private function stopThread(string $threadScope): void
    {
        foreach ($this->findThreadProcesses($threadScope) as $pid => $process)
        {
            shell_exec("kill -5 $pid 2>/dev/null");   // signal 5 (SIGTRAP): its default action terminates the process

            $this->logMsg("Kill process [$pid]: $process");
            $this->logMsg("");
        }
    }

    /**
     * Threads of THIS installation only: matched by the absolute path of commands/run.
     *
     * @return array<int, string> pid => command line
     */
    private function findThreadProcesses(string $threadScope): array
    {
        $runPath = realpath(VssCfg::cfg()->pathTopCore).'/commands/run';

        return ProcessList::FindContaining("$runPath /discord/bot/start/$threadScope");
    }
}
