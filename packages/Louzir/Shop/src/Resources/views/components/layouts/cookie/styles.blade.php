    <style>
        .louzir-cookie-consent {
            width: min(420px, calc(100vw - 32px));
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 20px;
            max-height: calc(100svh - 32px);
            overflow-y: auto;
            color: #111827;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.16);
        }

        .louzir-cookie-consent .cookie-title {
            color: #111827;
            font-size: 1.3rem;
            line-height: 1.25;
            letter-spacing: 0;
            font-weight: 700;
        }

        .louzir-cookie-consent .cookie-description {
            color: #4b5563;
            font-size: 0.9rem;
            line-height: 1.65;
            margin: 0;
        }

        .louzir-cookie-consent .cookie-description a {
            color: #111827;
            text-decoration: underline;
            text-underline-offset: 0.2em;
            font-weight: 600;
        }

        .louzir-cookie-consent .cookie-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .louzir-cookie-consent button,
        .louzir-cookie-consent .cookie-customize {
            border-radius: 4px;
            padding: 0.8rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 700;
            text-align: center;
            transition: background-color 0.2s ease;
        }

        .louzir-cookie-consent .cookie-accept {
            background: #202222;
            color: #fff;
            border: none;
            box-shadow: 0 10px 24px rgba(17, 24, 39, 0.18);
        }

        .louzir-cookie-consent .cookie-reject,
        .louzir-cookie-consent .cookie-customize {
            border: 1px solid rgba(17, 24, 39, 0.12);
            background: rgba(255, 255, 255, 0.7);
            color: #111827;
        }

        .louzir-cookie-consent .cookie-customize {
            display: block;
            margin-top: 0.75rem;
            width: 100%;
        }

        .louzir-cookie-consent .cookie-accept:hover,
        .louzir-cookie-consent .cookie-reject:hover,
        .louzir-cookie-consent .cookie-customize:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .louzir-cookie-consent {
                width: calc(100vw - 32px);
                border-radius: 8px;
                padding: 1rem;
            }

            .louzir-cookie-consent .cookie-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
