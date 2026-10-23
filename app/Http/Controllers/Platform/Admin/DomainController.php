<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\Domain;
use App\Repositories\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;

class DomainController extends Controller
{
    protected EntityManagerInterface $em;
    protected DomainRepository $domainRepository;

    public function __construct(EntityManagerInterface $entityManager, DomainRepository $domainRepository)
    {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->domainRepository = $domainRepository;
        $this->em = $entityManager;
    }

    public function index()
    {
        $domains = $this->domainRepository->findAllOrdered('id', 'asc', 20, Paginator::resolveCurrentPage());

        return view('platform.admin.domains.index')->with([
            'domains' => $domains,
        ]);
    }

    public function create()
    {
        $domain = new Domain();
        $domain->setUrl('');

        return view('platform.admin.domains.create')->with([
            'domain' => $domain,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'url' => $this->urlRules(),
            'public' => 'required|boolean',
        ]);

        $domain = new Domain();
        $domain->setUrl($attributes['url']);
        $domain->setPublic($attributes['public']);
        $this->em->persist($domain);
        $this->em->flush();

        return redirect()->route('platform.admin.domains.index')
            ->with('success', 'Domain created.');
    }

    public function show(Domain $domain)
    {
        Session::reflash();

        return redirect()->route('platform.admin.domains.edit', $domain);
    }

    public function edit(Domain $domain)
    {
        return view('platform.admin.domains.edit')->with([
            'domain' => $domain,
        ]);
    }

    public function update(Request $request, Domain $domain)
    {
        $attributes = $this->validate($request, [
            'url' => $this->urlRules(),
            'public' => 'boolean',
        ]);

        $domain->setUrl($attributes['url']);
        if (isset($attributes['public'])) {
            $domain->setPublic($attributes['public']);
        }
        $this->em->flush();

        return redirect()->route('platform.admin.domains.index')
            ->with('success', 'Domain updated.');
    }

    /**
     * Domain URLs are rendered into links, so only allow an http(s) scheme and host with a trailing slash.
     */
    protected function urlRules(): array
    {
        return [
            'required',
            'string',
            'max:250',
            'url:http,https',
            'regex:#^https?://[a-z0-9.-]+(:[0-9]+)?/$#i',
        ];
    }

    public function destroy(Domain $domain)
    {
        if (!empty($domain->getUrls())) {
            return redirect()->back()
                ->with('error', 'There are currently URLs associated with this domain.');
        }

        $this->em->remove($domain);
        $this->em->flush();

        return redirect()->route('platform.admin.domains.index')
            ->with('success', 'Domain deleted.');
    }
}
