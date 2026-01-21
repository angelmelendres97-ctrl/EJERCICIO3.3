<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido | Sistema de Compras</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="min-h-screen">
        <section class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white">
            <div class="mx-auto max-w-6xl px-6 py-16 lg:py-24">
                <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-widest text-slate-300">Bienvenidos</p>
                        <h1 class="mt-3 text-4xl font-bold leading-tight lg:text-5xl">Gestione sus órdenes de compra con claridad y control.</h1>
                        <p class="mt-4 text-lg text-slate-200">
                            En nuestra empresa impulsamos procesos ágiles y transparentes. Este sistema centraliza la creación,
                            seguimiento y aprobación de órdenes para que su equipo trabaje de forma eficiente.
                        </p>
                        <div class="mt-8">
                            <a
                                href="/admin"
                                class="inline-flex items-center justify-center rounded-lg bg-emerald-500 px-8 py-4 text-lg font-semibold text-white shadow-lg transition hover:bg-emerald-400"
                            >
                                Ir al panel de administración
                            </a>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-8 shadow-xl backdrop-blur">
                        <h2 class="text-2xl font-semibold">Información general</h2>
                        <ul class="mt-6 space-y-4 text-slate-200">
                            <li class="flex gap-3">
                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-sm font-bold">1</span>
                                Registro centralizado de proveedores y productos.
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-sm font-bold">2</span>
                                Control de presupuestos y aprobación por áreas.
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-sm font-bold">3</span>
                                Resumenes claros con desglose de impuestos.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-6 py-12">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow">
                    <h3 class="text-xl font-semibold">Sobre la empresa</h3>
                    <p class="mt-3 text-sm text-gray-600">
                        Somos un equipo comprometido con la excelencia operativa. Nuestra misión es brindar herramientas que
                        simplifiquen la gestión administrativa y fortalezcan la toma de decisiones.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow">
                    <h3 class="text-xl font-semibold">¿Cómo funciona?</h3>
                    <p class="mt-3 text-sm text-gray-600">
                        Registre la orden, agregue productos, confirme los impuestos y genere reportes en tiempo real. El sistema
                        se adapta a su flujo de trabajo y mantiene trazabilidad de cada acción.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow">
                    <h3 class="text-xl font-semibold">Secciones de ayuda</h3>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li>• Guía rápida para crear una orden.</li>
                        <li>• Preguntas frecuentes sobre presupuestos.</li>
                        <li>• Contacto de soporte interno.</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
