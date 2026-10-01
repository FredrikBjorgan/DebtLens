import { useState } from 'react';
import type { FormEvent } from 'react';
import { analyzeCode } from './api';
import type { AnalysisResult, Finding } from './api';
import { exampleCode } from './example';

const ruleNames: Record<string, string> = {
  too_many_parameters: 'Too many parameters',
  cyclomatic_complexity: 'Cyclomatic complexity',
  long_method: 'Long method',
  large_class: 'Large class',
};

function FindingCard({ finding }: { finding: Finding }) {
  return (
    <article className={`finding finding-${finding.severity}`}>
      <div className="finding-heading">
        <h3>{ruleNames[finding.rule] ?? finding.rule.replaceAll('_', ' ')}</h3>
        <span className={`badge ${finding.severity}`}>{finding.severity}</span>
      </div>
      <p>{finding.message}</p>
      {(finding.className || finding.methodName) && (
        <div className="location">
          {finding.className && <span>Class <code>{finding.className}</code></span>}
          {finding.methodName && <span>Method <code>{finding.methodName}</code></span>}
        </div>
      )}
      <dl className="finding-details">
        <div><dt>Line</dt><dd>{finding.line ?? '—'}</dd></div>
        <div><dt>Actual value</dt><dd>{finding.actualValue ?? '—'}</dd></div>
        <div><dt>Threshold</dt><dd>{finding.threshold ?? '—'}</dd></div>
      </dl>
    </article>
  );
}

export default function App() {
  const [code, setCode] = useState(exampleCode);
  const [result, setResult] = useState<AnalysisResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  function updateCode(value: string) {
    setCode(value);
    setResult(null);
    setError(null);
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (loading) return;
    setError(null);
    setResult(null);

    if (!code.trim()) {
      setError('Add some PHP code before running an analysis.');
      return;
    }

    setLoading(true);
    try {
      setResult(await analyzeCode(code));
    } catch (error) {
      setError(error instanceof Error ? error.message : 'Something went wrong. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="app">
      <header className="header">
        <a className="brand" href="/" aria-label="DebtLens home">
          <span className="brand-mark" aria-hidden="true">{'</>'}</span>
          <span>DebtLens</span>
        </a>
        <span className="header-description">PHP Technical Debt Analyzer</span>
      </header>

      <main>
        <div className="intro">
          <span className="eyebrow">A clearer view of your code</span>
          <h1>Find the friction in your PHP.</h1>
          <p>Paste your code. Spot complexity, oversized methods, and other places to simplify.</p>
        </div>

        <div className="workspace">
          <form className="panel editor-panel" onSubmit={handleSubmit}>
            <div className="panel-heading">
              <div><h2>Source code</h2><p>Start with the example or paste your own.</p></div>
              <button className="text-button" type="button" disabled={loading} onClick={() => updateCode(exampleCode)}>Load example</button>
            </div>
            <div className="editor-toolbar"><label htmlFor="php-code">PHP</label><span>{code.split('\n').length} lines</span></div>
            <textarea
              id="php-code"
              aria-label="PHP source code"
              aria-describedby={error ? 'analysis-error' : 'code-help'}
              aria-invalid={Boolean(error)}
              value={code}
              onChange={(event) => updateCode(event.target.value)}
              disabled={loading}
              spellCheck={false}
              autoCapitalize="off"
              autoCorrect="off"
              wrap="off"
            />
            <div className="editor-footer">
              <p id="code-help">Include the <code>{'<?php'}</code> opening tag.</p>
              <button className="analyze-button" type="submit" disabled={loading}>
                {loading && <span className="spinner" aria-hidden="true" />}
                {loading ? 'Analyzing…' : 'Analyze code'}
                {!loading && <span aria-hidden="true">→</span>}
              </button>
            </div>
            {error && <p className="error" id="analysis-error" role="alert">{error}</p>}
          </form>

          <section className="results" aria-labelledby="results-heading" aria-busy={loading}>
            <div className="results-heading"><h2 id="results-heading">Analysis results</h2><span>4 rules checked</span></div>
            <div className="summary">
              {(['total', 'high', 'medium', 'low'] as const).map((key) => (
                <div className={`stat stat-${key}`} key={key}>
                  <span>{key === 'total' ? 'Total findings' : key}</span>
                  <strong>{result?.summary[key] ?? '—'}</strong>
                </div>
              ))}
            </div>
            <div className="result-content" aria-live="polite">
              {loading ? (
                <div className="empty-state"><span className="state-symbol" aria-hidden="true">…</span><h3>Taking a closer look</h3><p>Checking your PHP against all four rules.</p></div>
              ) : result ? (
                result.findings.length ? (
                  <div className="findings-list">{result.findings.map((finding, index) => <FindingCard key={index} finding={finding} />)}</div>
                ) : (
                  <div className="empty-state success"><span className="state-symbol" aria-hidden="true">✓</span><h3>No findings</h3><p>Your code is within the thresholds of all four rules.</p></div>
                )
              ) : (
                <div className="empty-state"><span className="state-symbol" aria-hidden="true">{'{ }'}</span><h3>{error ? 'Analysis needs attention' : 'Your next improvement starts here'}</h3><p>{error ? 'Resolve the error and run the analysis again.' : 'Run an analysis to see findings and their severity.'}</p></div>
              )}
            </div>
          </section>
        </div>
        <footer className="footer"><span>Small changes. Healthier code.</span><span>Static analysis · Your code is never executed.</span></footer>
      </main>
    </div>
  );
}
