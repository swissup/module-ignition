<?php

namespace Swissup\Ignition\Model;

use Closure;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class CensorSensitiveData implements FlareMiddleware
{
    private const CENSORED = '<CENSORED>';

    private const SENSITIVE_KEY = '/pass|pwd|secret|token|auth|cookie|session|key|cc_|card|cvv|ssn/i';

    public function handle(Report $report, Closure $next)
    {
        $context = $report->allContext();

        foreach (['cookies', 'session'] as $group) {
            if (!empty($context[$group]) && is_array($context[$group])) {
                $context[$group] = array_map(fn () => self::CENSORED, $context[$group]);
            }
        }

        if (!empty($context['headers']) && is_array($context['headers'])) {
            $context['headers'] = $this->censor($context['headers']);
        }

        foreach (['body', 'queryString'] as $group) {
            if (!empty($context['request_data'][$group]) && is_array($context['request_data'][$group])) {
                $context['request_data'][$group] = $this->censor($context['request_data'][$group]);
            }
        }

        // URL queries may contain tokens, e.g. ?token= in password reset links.
        // The request query is already reported (censored) in request_data.queryString
        if (isset($context['request']['url']) && is_string($context['request']['url'])) {
            $context['request']['url'] = explode('?', $context['request']['url'], 2)[0];
        }

        if (isset($context['headers']['referer']) && is_string($context['headers']['referer'])) {
            $context['headers']['referer'] = explode('?', $context['headers']['referer'], 2)[0];
        }

        $report->userProvidedContext($context);

        return $next($report);
    }

    private function censor(array $data): array
    {
        foreach ($data as $key => $value) {
            if (preg_match(self::SENSITIVE_KEY, (string) $key)) {
                $data[$key] = self::CENSORED;
            } elseif (is_array($value)) {
                $data[$key] = $this->censor($value);
            }
        }

        return $data;
    }
}
