export type Severity = 'high' | 'medium' | 'low';

export interface Finding {
  rule: string;
  severity: Severity;
  message: string;
  className: string | null;
  methodName: string | null;
  line: number | null;
  actualValue: number | null;
  threshold: number | null;
}

export interface AnalysisResult {
  summary: { total: number; high: number; medium: number; low: number };
  findings: Finding[];
}

export async function analyzeCode(code: string): Promise<AnalysisResult> {
  let response: Response;

  try {
    response = await fetch('/api/analyze', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code }),
      signal: AbortSignal.timeout(30_000),
    });
  } catch (error) {
    if (error instanceof DOMException && error.name === 'TimeoutError') {
      throw new Error('Analysis timed out. Try a smaller PHP example.');
    }
    throw new Error('Cannot reach the API. Check that the PHP backend is running on port 8000.');
  }

  let data;
  try {
    data = await response.json();
  } catch {
    throw new Error('The API returned an unreadable response. Check that the PHP backend is running.');
  }

  if (!response.ok) {
    const message = typeof data?.error?.message === 'string'
      ? data.error.message
      : 'Analysis failed. Check that the PHP backend is running on port 8000.';
    throw new Error(response.status === 422 ? `Invalid PHP: ${message}` : message);
  }

  if (!data?.summary || !Array.isArray(data.findings)
    || !['total', 'high', 'medium', 'low'].every((key) => typeof data.summary[key] === 'number')) {
    throw new Error('The API returned an unexpected response. Please try again.');
  }

  return data as AnalysisResult;
}
