# Builds the audit summary from Lighthouse reports (LHR JSON).
#
# Input: an array of LHRs (`jq -s`), several runs per page.
# Arguments: $base (checked URL without a trailing slash), $mode, $sha,
# $generated_at, $expected (JSON array of the page paths to be measured) and
# $reason (empty, or why the workflow got no reports; then the input is unused,
# run with `jq -n`).
# Output: the summary the audit agent reads. Without usable data the status is
# `no-data` with a reason; otherwise `ok` with pages, metrics and findings.
#
# Per page, every value is the median of its runs, so a single noisy run does
# not become a finding. A finding is a Performance audit with a median score
# below 0.9 on at least one page, or a metric worse than the "good" Core Web
# Vitals threshold. TBT stands in for INP, which a lab run does not measure.

def metric_specs:
  [
    {key: "lcp", audit: "largest-contentful-paint", title: "Largest Contentful Paint", unit: "ms", good: 2500},
    {key: "cls", audit: "cumulative-layout-shift", title: "Cumulative Layout Shift", unit: "unitless", good: 0.1},
    {key: "tbt", audit: "total-blocking-time", title: "Total Blocking Time", unit: "ms", good: 200}
  ];

# Audits without a pass or fail verdict.
def unscored_modes: ["informative", "manual", "notApplicable", "error"];

def median: sort | .[(length - 1) / 2 | floor];

def page_path: .requestedUrl | ltrimstr($base) | if . == "" then "/" else . end;

# Milliseconds to whole tens and CLS to three decimals keep week-to-week
# jitter out of the values the agent compares.
def round_metric($unit): if $unit == "ms" then (. / 10 | round) * 10 else (. * 1000 | round) / 1000 end;

def no_data($reason):
  {mode: $mode, status: "no-data", reason: $reason, url: $base, sha: $sha, generated_at: $generated_at, pages: [], findings: []};

# Per page: the median metrics and the audits whose median score fails.
def page_summary:
  . as $runs
  | ($runs[0] | page_path) as $path
  | (metric_specs | map(.audit)) as $metric_audits
  | {
      path: $path,
      runs: ($runs | length),
      metrics: (
        metric_specs
        | map(. as $spec | {key: $spec.key, value: ([$runs[].audits[$spec.audit].numericValue | numbers] | if length == 0 then null else median | round_metric($spec.unit) end)})
        | from_entries
      ),
      failed_audits: [
        $runs[0].categories.performance.auditRefs[]
        | select(.group != "hidden")
        | .id as $id
        | select($metric_audits | index($id) | not)
        | [$runs[].audits[$id] | select(.score != null and (.scoreDisplayMode as $m | unscored_modes | index($m) | not))] as $scored
        | select($scored | length > 0)
        | ($scored | map(.score) | median) as $score
        | select($score < 0.9)
        | ($scored | map(select(.score == $score)) | first) as $typical
        | {
            id: $id,
            title: $typical.title,
            description: $typical.description,
            page: {path: $path, score: $score, display_value: ($typical.displayValue // null), metric_savings: ($typical.metricSavings // null)}
          }
      ]
    };

if $reason != "" then
  no_data($reason)
else
. as $reports
| ($reports | map(select(.runtimeError != null))) as $errored
| if ($reports | length) == 0 then
    no_data("Lighthouse не вернул ни одного отчёта")
  elif ($errored | length) > 0 then
    no_data("Lighthouse вернул ошибку: " + ($errored | map("\(page_path): \(.runtimeError.code)") | unique | join(", ")))
  else
    ($reports | group_by(page_path) | map(page_summary)) as $pages
    | ($expected - ($pages | map(.path))) as $missing
    | if ($missing | length) > 0 then
        no_data("Нет отчёта по страницам: " + ($missing | join(", ")))
      else
        {
          mode: $mode,
          status: "ok",
          reason: null,
          url: $base,
          sha: $sha,
          generated_at: $generated_at,
          pages: ($pages | map({path, runs, metrics})),
          findings: (
            (
              [$pages[].failed_audits[]]
              | group_by(.id)
              | map({
                  key: "lighthouse/\(.[0].id)",
                  title: .[0].title,
                  description: .[0].description,
                  pages: map(.page)
                })
            )
            + (
              metric_specs
              | map(
                  . as $spec
                  | [$pages[] | {path, value: .metrics[$spec.key]} | select(.value != null and .value > $spec.good)] as $slow
                  | select($slow | length > 0)
                  | {
                      key: "metric/\($spec.key)",
                      title: "\($spec.title) worse than the good threshold",
                      description: "Median \($spec.title) is above the good Core Web Vitals threshold of \($spec.good)\(if $spec.unit == "ms" then " ms" else "" end).",
                      threshold: $spec.good,
                      unit: $spec.unit,
                      pages: $slow
                    }
                )
            )
            | sort_by(.key)
          )
        }
      end
  end
end
