# AI Employees

AI Employees are persistent digital workers represented like team members inside an Organization.

They are not simple agents, prompts, API keys, provider accounts, or model names. An AI Employee has an identity that survives changes to the Brain / Model used at runtime.

## Profile

Each AI Employee should have:

- Full name
- Employee code
- Avatar
- Role title
- Organization
- Department
- Manager
- Bio
- Job description
- Mission
- Responsibilities
- Languages
- Communication style
- Personality profile
- Permissions
- Capabilities
- Assigned Brain / Model configuration
- Memory settings
- Current Assignments
- Assignment Logs
- Employee Messages
- Performance Metrics
- Audit history

## Identity Versus Brain / Model

The AI Employee is the durable identity. The Brain / Model is the current runtime configuration used to perform work.

Example: Mila Andonova remains the same AI Employee even if she uses a mock provider during MVP development, OpenAI later, and another model provider in the future.

## Initial AI Employees

| AI Employee | Department | Role | Mission |
| --- | --- | --- | --- |
| Elena Markova | Editorial | Editor-in-Chief AI | Ensure editorial quality and prepare content for human approval. |
| Martin Nikolovski | Research | Researcher AI | Find reliable sources, extract facts, and prepare research packages. |
| Mila Andonova | Writing | Macedonian Writer AI | Write original Macedonian articles based on approved research. |
| Sara Ilieva | Localization | Translator / Localization AI | Convert foreign-language information into natural Macedonian context. |
| Viktor Petrov | SEO | SEO AI | Optimize content for search, discovery, CTR, and structured metadata. |
| David Kostovski | Trust & Safety | Fact Checker AI | Verify claims, detect unsupported statements, and flag risk. |

## MVP Guardrails

- AI Employees may create drafts, reports, recommendations, and metadata suggestions.
- AI Employees may request human review.
- AI Employees must not publish directly in the MVP.
- AI Employees must escalate low-confidence, sensitive, conflicting, or policy-bound work.
- Every important employee action must be logged.
